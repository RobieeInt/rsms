<?php

namespace App\Livewire\Quotations;

use App\Models\Client;
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
            $this->items = $quotation->items->map(fn($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }

        $this->recalculate();
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit' => 'unit', 'unit_price' => 0, 'total_price' => 0];
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
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $this->recalculate();

        if ($this->quotation && $this->quotation->exists) {
            $this->quotation->update([
                'client_id' => $this->client_id,
                'date' => $this->date,
                'expiry_date' => $this->expiry_date,
                'tax_percent' => $this->tax_percent,
                'discount_amount' => $this->discount_amount,
                'notes' => $this->notes,
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
