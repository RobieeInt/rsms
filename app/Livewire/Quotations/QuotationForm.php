<?php

namespace App\Livewire\Quotations;

use App\Models\Client;
use App\Models\Finding;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\QuotationService;
use Livewire\Component;

class QuotationForm extends Component
{
    public ?Quotation $quotation = null;

    public int $client_id = 0;
    public string $date = '';
    public string $expiry_date = '';
    public float $tax_percent = 0;
    public float $discount_amount = 0;
    public string $notes = '';
    public array $items = [];
    public float $subtotal = 0;
    public float $tax_amount = 0;
    public float $total_amount = 0;

    /** Skema pembayaran: [['label' => 'DP', 'percent' => 30], ...]. Kosong = bayar penuh. */
    public array $payment_terms = [];

    public function mount(?Quotation $quotation = null): void
    {
        $this->date = now()->format('Y-m-d');
        $this->expiry_date = now()->addDays(30)->format('Y-m-d');

        if ($quotation && $quotation->exists) {
            $this->quotation = $quotation;
            $this->client_id = $quotation->client_id;
            $this->date = $quotation->date->format('Y-m-d');
            $this->expiry_date = $quotation->expiry_date->format('Y-m-d');
            $this->tax_percent = (float) $quotation->tax_percent;
            $this->discount_amount = (float) $quotation->discount_amount;
            $this->notes = $quotation->notes ?? '';
            $this->payment_terms = collect($quotation->payment_terms ?? [])
                ->map(fn ($t) => ['label' => (string) ($t['label'] ?? ''), 'percent' => (float) ($t['percent'] ?? 0)])
                ->all();
            // cost_price is only ever loaded into this (public, browser-visible)
            // component state for admins — a technician must never see it even
            // via page source, not just have the input hidden.
            $isAdmin = auth()->user()->hasRole('admin');
            $this->items = $quotation->items->map(fn($item) => [
                'description' => $item->description,
                'detail' => $item->detail,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'cost_price' => $isAdmin && $item->cost_price !== null ? (float) $item->cost_price : null,
                'discount_amount' => (float) $item->discount_amount,
                'total_price' => (float) $item->total_price,
            ])->toArray();
        }

        // "Create Quotation" from a Finding (finding-show.blade.php) links
        // here with ?finding=ID — prefill the client + a starter line item
        // from it instead of leaving the admin to retype it from scratch.
        // Only applies when creating fresh; never overrides an existing
        // quotation being edited.
        if (! $this->quotation && ($findingId = request()->integer('finding'))) {
            $finding = Finding::with('recommendations')->find($findingId);

            if ($finding) {
                $this->client_id = $finding->client_id;
                $this->items[] = [
                    'description' => $finding->title . ($finding->recommendations->first()
                        ? ': ' . $finding->recommendations->first()->recommendation
                        : ''),
                    'detail' => $finding->description,
                    'quantity' => 1,
                    'unit' => 'unit',
                    'unit_price' => 0,
                    'cost_price' => null,
                    'discount_amount' => 0,
                    'total_price' => 0,
                    'finding_id' => $finding->id,
                ];
            }
        }

        if (empty($this->items)) {
            $this->addItem();
        }

        $this->recalculate();
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'detail' => '', 'quantity' => 1, 'unit' => 'unit', 'unit_price' => 0, 'cost_price' => null, 'discount_amount' => 0, 'total_price' => 0];
    }

    public function addPaymentTerm(): void
    {
        $used = collect($this->payment_terms)->sum(fn ($t) => (float) $t['percent']);
        $this->payment_terms[] = [
            'label' => empty($this->payment_terms) ? 'DP' : 'Termin ' . (count($this->payment_terms) + 1),
            'percent' => max(0, round(100 - $used, 2)),
        ];
    }

    public function removePaymentTerm(int $index): void
    {
        array_splice($this->payment_terms, $index, 1);
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->recalculate();
    }

    public function updatedItems(): void { $this->recalculate(); }
    public function updatedTaxPercent(): void { $this->recalculate(); }
    public function updatedDiscountAmount(): void { $this->recalculate(); }

    public function updateItemTotal(int $index): void
    {
        $item = $this->items[$index];
        $this->items[$index]['total_price'] = round((float)$item['quantity'] * (float)$item['unit_price'] - (float)$item['discount_amount'], 2);
        $this->recalculate();
    }

    private function recalculate(): void
    {
        foreach ($this->items as $i => $item) {
            $this->items[$i]['total_price'] = round((float)$item['quantity'] * (float)$item['unit_price'] - (float)$item['discount_amount'], 2);
        }
        $this->subtotal = round(collect($this->items)->sum('total_price'), 2);
        $this->tax_amount = round($this->subtotal * ($this->tax_percent / 100), 2);
        $this->total_amount = round($this->subtotal + $this->tax_amount - $this->discount_amount, 2);
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'payment_terms' => 'array',
            'payment_terms.*.label' => 'required|string|max:100',
            'payment_terms.*.percent' => 'required|numeric|min:0.01|max:100',
        ], [
            'payment_terms.*.label.required' => 'Nama termin wajib diisi.',
            'payment_terms.*.percent.min' => 'Persen minimal 0,01.',
        ]);

        if (! empty($this->payment_terms)) {
            $sum = round(collect($this->payment_terms)->sum(fn ($t) => (float) $t['percent']), 2);
            if (abs($sum - 100) > 0.001) {
                $this->addError('payment_terms', 'Total persen termin harus 100% (sekarang ' . Quotation::formatPercent($sum) . '%).');
                return;
            }
        }
        $paymentTerms = empty($this->payment_terms) ? null : collect($this->payment_terms)
            ->map(fn ($t) => ['label' => trim($t['label']), 'percent' => (float) $t['percent']])
            ->values()->all();

        $this->recalculate();

        // Cost/"modal" documentation is admin-only. The form never gives a
        // non-admin the input to change it, but a tampered request could
        // still inject a value — so a non-admin's save always reverts every
        // item's cost_price back to whatever is already in the DB (not to
        // null), so an unrelated edit (e.g. fixing a typo) can't silently
        // wipe out cost data an admin already documented.
        if (! auth()->user()->hasRole('admin')) {
            $existingCosts = ($this->quotation && $this->quotation->exists)
                ? $this->quotation->items()->orderBy('sort_order')->pluck('cost_price')->all()
                : [];
            foreach ($this->items as $i => $item) {
                $this->items[$i]['cost_price'] = $existingCosts[$i] ?? null;
            }
        }

        if ($this->quotation && $this->quotation->exists) {
            $this->quotation->update([
                'client_id' => $this->client_id,
                'date' => $this->date,
                'expiry_date' => $this->expiry_date,
                'tax_percent' => $this->tax_percent,
                'discount_amount' => $this->discount_amount,
                'notes' => $this->notes,
                'payment_terms' => $paymentTerms,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
            ]);
            $this->quotation->items()->delete();
            foreach ($this->items as $i => $item) {
                QuotationItem::create(array_merge($item, ['quotation_id' => $this->quotation->id, 'sort_order' => $i]));
            }
            session()->flash('success', 'Quotation updated.');
            $this->redirect(route('quotations.show', $this->quotation));
        } else {
            $quotation = Quotation::create([
                'client_id' => $this->client_id,
                'created_by' => auth()->id(),
                'quotation_number' => Quotation::generateNumber(),
                'approval_token' => Quotation::generateToken(),
                'date' => $this->date,
                'expiry_date' => $this->expiry_date,
                'tax_percent' => $this->tax_percent,
                'discount_amount' => $this->discount_amount,
                'notes' => $this->notes,
                'payment_terms' => $paymentTerms,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
            ]);
            foreach ($this->items as $i => $item) {
                QuotationItem::create(array_merge($item, ['quotation_id' => $quotation->id, 'sort_order' => $i]));
            }
            session()->flash('success', 'Quotation created.');
            $this->redirect(route('quotations.show', $quotation));
        }
    }

    public function render()
    {
        $isEdit = $this->quotation && $this->quotation->exists;
        $clients = Client::where('is_active', true)->orderBy('company_name')->get();

        return view('livewire.quotations.quotation-form', compact('isEdit', 'clients'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Quotation' : 'New Quotation']);
    }
}
