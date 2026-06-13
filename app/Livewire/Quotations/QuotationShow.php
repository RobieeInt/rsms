<?php

namespace App\Livewire\Quotations;

use App\Models\Quotation;
use App\Services\InvoiceService;
use App\Services\QuotationService;
use Livewire\Component;

class QuotationShow extends Component
{
    public Quotation $quotation;

    public function mount(Quotation $quotation): void
    {
        $this->quotation = $quotation;
    }

    public function send(): void
    {
        app(QuotationService::class)->send($this->quotation);
        $this->quotation->refresh();
        $this->dispatch('notify', message: 'Quotation sent to client.', type: 'success');
    }

    public function convertToInvoice(): void
    {
        if ($this->quotation->status !== 'approved') {
            $this->dispatch('notify', message: 'Only approved quotations can be converted.', type: 'error');
            return;
        }

        $invoice = app(InvoiceService::class)->createFromQuotation($this->quotation, auth()->id());
        session()->flash('success', 'Invoice created from quotation.');
        $this->redirect(route('invoices.show', $invoice));
    }

    public function render()
    {
        $this->quotation->load(['client', 'creator', 'items', 'invoice']);

        return view('livewire.quotations.quotation-show')
            ->layout('layouts.app', ['title' => $this->quotation->quotation_number]);
    }
}
