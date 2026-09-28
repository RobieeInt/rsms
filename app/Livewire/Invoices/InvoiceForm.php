<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Services\InvoiceService;
use Livewire\Component;

class InvoiceForm extends Component
{
    public ?Invoice $invoice = null;

    public int $client_id = 0;
    public string $type = 'manual';
    public string $invoice_date = '';
    public string $due_date = '';
    public float $tax_percent = 0;
    public float $discount_amount = 0;
    public string $notes = '';
    public array $items = [];
    public float $subtotal = 0;
    public float $tax_amount = 0;
    public float $total_amount = 0;

    // Termin (type = quotation, create only): the invoice is billed as a
    // slice of the selected quotation's remaining balance via InvoiceService.
    public int $quotation_id = 0;
    public string $termin_amount = '';
    public string $termin_description = '';

    public function mount(?Invoice $invoice = null): void
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');

        if ($invoice && $invoice->exists) {
            $this->invoice = $invoice;
            $this->client_id = $invoice->client_id;
            $this->type = $invoice->type;
            $this->invoice_date = $invoice->invoice_date->format('Y-m-d');
            $this->due_date = $invoice->due_date->format('Y-m-d');
            $this->tax_percent = (float) $invoice->tax_percent;
            $this->discount_amount = (float) $invoice->discount_amount;
            $this->notes = $invoice->notes ?? '';
            // cost_price is only ever loaded into this (public, browser-visible)
            // component state for admins — a technician must never see it even
            // via page source, not just have the input hidden.
            $isAdmin = auth()->user()->hasRole('admin');
            $this->items = $invoice->items->map(fn($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'cost_price' => $isAdmin && $item->cost_price !== null ? (float) $item->cost_price : null,
                'total_price' => (float) $item->total_price,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
        $this->recalculate();
    }

    public function updatedType(): void
    {
        $this->resetErrorBag();
        if ($this->type !== 'quotation') {
            $this->quotation_id = 0;
            $this->termin_amount = '';
        }
    }

    public function updatedQuotationId(): void
    {
        $this->resetErrorBag();
        $quotation = $this->selectedQuotation();
        $this->termin_amount = $quotation ? number_format($quotation->remainingBalance(), 2, '.', '') : '';
    }

    private function selectedQuotation(): ?Quotation
    {
        return $this->quotation_id
            ? Quotation::with(['client', 'invoices'])->where('status', 'approved')->find($this->quotation_id)
            : null;
    }

    private function isTerminMode(): bool
    {
        return $this->type === 'quotation' && ! ($this->invoice && $this->invoice->exists);
    }

    private function saveTermin(): void
    {
        $this->validate([
            'quotation_id' => 'required|integer|min:1',
            'termin_amount' => 'required|numeric|min:0.01',
            'termin_description' => 'nullable|string|max:500',
        ], [
            'quotation_id.min' => 'Pilih penawaran yang mau ditagih.',
        ]);

        $quotation = $this->selectedQuotation();
        if (! $quotation) {
            $this->addError('quotation_id', 'Penawaran tidak ditemukan atau belum disetujui.');
            return;
        }

        $remaining = $quotation->remainingBalance();
        if ($remaining <= 0) {
            $this->addError('quotation_id', 'Penawaran ini sudah ditagih penuh.');
            return;
        }
        if ((float) $this->termin_amount > $remaining + 0.01) {
            $this->addError('termin_amount', 'Jumlah melebihi sisa saldo penawaran (Rp ' . number_format($remaining, 0, ',', '.') . ').');
            return;
        }

        $invoice = app(InvoiceService::class)->createInstallmentFromQuotation(
            $quotation,
            auth()->id(),
            (float) $this->termin_amount,
            $this->termin_description ?: null
        );

        session()->flash('success', 'Invoice termin berhasil dibuat.');
        $this->redirect(route('invoices.show', $invoice));
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit' => 'unit', 'unit_price' => 0, 'cost_price' => null, 'total_price' => 0];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->recalculate();
    }

    public function updatedTaxPercent(): void { $this->recalculate(); }
    public function updatedDiscountAmount(): void { $this->recalculate(); }

    public function updateItemTotal(int $index): void
    {
        $item = $this->items[$index];
        $this->items[$index]['total_price'] = round((float)$item['quantity'] * (float)$item['unit_price'], 2);
        $this->recalculate();
    }

    private function recalculate(): void
    {
        foreach ($this->items as $i => $item) {
            $this->items[$i]['total_price'] = round((float)$item['quantity'] * (float)$item['unit_price'], 2);
        }
        $this->subtotal = round(collect($this->items)->sum('total_price'), 2);
        $this->tax_amount = round($this->subtotal * ($this->tax_percent / 100), 2);
        $this->total_amount = round($this->subtotal + $this->tax_amount - $this->discount_amount, 2);
    }

    public function save(): void
    {
        if ($this->isTerminMode()) {
            $this->saveTermin();
            return;
        }

        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
        ]);

        $this->recalculate();

        // Cost/"modal" documentation is admin-only. The form never gives a
        // non-admin the input to change it, but a tampered request could
        // still inject a value — so a non-admin's save always reverts every
        // item's cost_price back to whatever is already in the DB (not to
        // null), so an unrelated edit (e.g. fixing a typo) can't silently
        // wipe out cost data an admin already documented.
        if (! auth()->user()->hasRole('admin')) {
            $existingCosts = ($this->invoice && $this->invoice->exists)
                ? $this->invoice->items()->orderBy('sort_order')->pluck('cost_price')->all()
                : [];
            foreach ($this->items as $i => $item) {
                $this->items[$i]['cost_price'] = $existingCosts[$i] ?? null;
            }
        }

        $data = [
            'client_id' => $this->client_id,
            'created_by' => auth()->id(),
            'type' => $this->type,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'tax_percent' => $this->tax_percent,
            'discount_amount' => $this->discount_amount,
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
        ];

        if ($this->invoice && $this->invoice->exists) {
            $this->invoice->update($data);
            $this->invoice->items()->delete();
            foreach ($this->items as $i => $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $this->invoice->id, 'sort_order' => $i]));
            }
            session()->flash('success', 'Invoice updated.');
            $this->redirect(route('invoices.show', $this->invoice));
        } else {
            $data['invoice_number'] = Invoice::generateNumber();
            $invoice = Invoice::create($data);
            foreach ($this->items as $i => $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id, 'sort_order' => $i]));
            }
            session()->flash('success', 'Invoice created.');
            $this->redirect(route('invoices.show', $invoice));
        }
    }

    public function render()
    {
        $isEdit = $this->invoice && $this->invoice->exists;
        $clients = Client::where('is_active', true)->orderBy('company_name')->get();

        $terminMode = $this->isTerminMode();
        $quotations = $terminMode
            ? Quotation::with(['client', 'invoices'])->where('status', 'approved')->latest('date')->get()
                ->filter(fn ($q) => $q->remainingBalance() > 0)
            : collect();
        $selectedQuotation = $terminMode ? $this->selectedQuotation() : null;
        $linkedQuotation = $isEdit ? $this->invoice->quotation : null;

        return view('livewire.invoices.invoice-form', compact('isEdit', 'clients', 'terminMode', 'quotations', 'selectedQuotation', 'linkedQuotation'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Invoice' : 'New Invoice']);
    }
}
