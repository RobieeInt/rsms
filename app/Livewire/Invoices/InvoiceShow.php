<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Notifications\InvoiceGeneratedNotification;
use App\Services\InvoiceService;
use Livewire\Component;
use Livewire\WithFileUploads;

class InvoiceShow extends Component
{
    use WithFileUploads;

    public Invoice $invoice;
    public bool $showPaymentModal = false;
    public string $payment_date = '';
    public string $payment_method = '';
    public string $payment_notes = '';
    public $payment_proof = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->payment_date = now()->format('Y-m-d');
    }

    public function send(): void
    {
        app(InvoiceService::class)->markAsSent($this->invoice);
        $this->invoice->refresh();
        $this->dispatch('notify', message: 'Invoice sent to client.', type: 'success');
    }

    public function resendEmail(): void
    {
        $email = $this->invoice->client->pic_email ?? null;
        if (!$email) {
            $this->dispatch('notify', message: 'Klien tidak memiliki alamat email.', type: 'error');
            return;
        }
        $this->invoice->client->notifyNow(new InvoiceGeneratedNotification($this->invoice));
        $this->invoice->logSend('sent', $email);
        $this->dispatch('notify', message: 'Email invoice dikirim ke ' . $email . '.', type: 'success');
    }

    public function markAsPaid(): void
    {
        $this->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payment_proof' => 'nullable|file|max:5120',
        ]);

        $data = [
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'payment_notes' => $this->payment_notes,
        ];

        if ($this->payment_proof) {
            $data['payment_proof'] = $this->payment_proof->store('payment-proofs', 'public');
        }

        app(InvoiceService::class)->markAsPaid($this->invoice, $data);
        $this->showPaymentModal = false;
        $this->invoice->refresh();
        $this->dispatch('notify', message: 'Invoice marked as paid.', type: 'success');
    }

    public function render()
    {
        $this->invoice->load(['client', 'creator', 'items', 'sendLogs']);

        return view('livewire.invoices.invoice-show')
            ->layout('layouts.app', ['title' => $this->invoice->invoice_number]);
    }
}
