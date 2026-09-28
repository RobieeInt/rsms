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
    public string $newInvoicePercent = '';
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
        $this->useScheduledTerm();
        $this->newInvoiceSendImmediately = false;
        $this->showInvoiceModal = true;
    }

    /** Isi modal dengan termin berikutnya sesuai skema (atau sisa saldo kalau nggak ada skema). */
    public function useScheduledTerm(): void
    {
        $next = $this->quotation->nextTerm();
        $this->setInvoiceAmount($next['amount']);
        $this->newInvoiceDescription = $next['label']
            ? sprintf('%s — %s', $next['label'], $this->quotation->quotation_number)
            : '';
    }

    public function payOffRemaining(): void
    {
        $this->setInvoiceAmount($this->quotation->remainingBalance());
        $this->newInvoiceDescription = 'Pelunasan — ' . $this->quotation->quotation_number;
    }

    public function updatedNewInvoicePercent(): void
    {
        $total = (float) $this->quotation->total_amount;
        if ($total > 0 && is_numeric($this->newInvoicePercent)) {
            $this->newInvoiceAmount = number_format(round($total * (float) $this->newInvoicePercent / 100, 2), 2, '.', '');
        }
    }

    public function updatedNewInvoiceAmount(): void
    {
        $total = (float) $this->quotation->total_amount;
        if ($total > 0 && is_numeric($this->newInvoiceAmount)) {
            $this->newInvoicePercent = (string) round((float) $this->newInvoiceAmount / $total * 100, 2);
        }
    }

    private function setInvoiceAmount(float $amount): void
    {
        $this->newInvoiceAmount = number_format($amount, 2, '.', '');
        $this->updatedNewInvoiceAmount();
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
