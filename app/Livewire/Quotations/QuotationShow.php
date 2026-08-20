<?php

namespace App\Livewire\Quotations;

use App\Models\Quotation;
use App\Services\InvoiceService;
use App\Services\QuotationService;
use Livewire\Component;

class QuotationShow extends Component
{
    public Quotation $quotation;

    public bool $showInvoiceModal = false;
    public string $newInvoiceAmount = '';
    public string $newInvoiceDescription = '';
    public bool $newInvoiceSendImmediately = false;

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

    public function openInvoiceModal(): void
    {
        $this->resetErrorBag();
        $this->newInvoiceAmount = number_format($this->quotation->remainingBalance(), 2, '.', '');
        $this->newInvoiceDescription = '';
        $this->newInvoiceSendImmediately = false;
        $this->showInvoiceModal = true;
    }

    public function createInvoice(): void
    {
        $this->validate([
            'newInvoiceAmount' => ['required', 'numeric', 'min:0.01'],
            'newInvoiceDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $remaining = $this->quotation->remainingBalance();

        if ((float) $this->newInvoiceAmount > $remaining + 0.01) {
            $this->addError('newInvoiceAmount', 'Jumlah melebihi sisa saldo penawaran (Rp ' . number_format($remaining, 0, ',', '.') . ').');
            return;
        }

        $invoice = app(InvoiceService::class)->createInstallmentFromQuotation(
            $this->quotation,
            auth()->id(),
            (float) $this->newInvoiceAmount,
            $this->newInvoiceDescription ?: null
        );

        if ($this->newInvoiceSendImmediately) {
            app(InvoiceService::class)->markAsSent($invoice);
        }

        $this->showInvoiceModal = false;
        session()->flash('success', 'Invoice termin berhasil dibuat.');
        $this->redirect(route('invoices.show', $invoice));
    }

    public function render()
    {
        $this->quotation->load(['client', 'creator', 'items', 'invoices']);

        return view('livewire.quotations.quotation-show')
            ->layout('layouts.app', ['title' => $this->quotation->quotation_number]);
    }
}
