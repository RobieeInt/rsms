<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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
            $this->items = $invoice->items->map(fn($item) => [
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
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
        ]);

        $this->recalculate();

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

        return view('livewire.invoices.invoice-form', compact('isEdit', 'clients'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Invoice' : 'New Invoice']);
    }
}
