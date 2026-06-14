<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Notifications\InvoiceGeneratedNotification;
use App\Services\InvoiceService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public string $typeFilter = '';
    public ?int $deleteId = null;

    public bool $showRetainerModal = false;
    public int $retainerClientId = 0;

    public function updatingSearch(): void { $this->resetPage(); }

    public function generateRetainerInvoice(InvoiceService $invoiceService): void
    {
        $this->validate(['retainerClientId' => 'required|exists:clients,id']);

        $client = Client::findOrFail($this->retainerClientId);

        if ($client->monthly_retainer_fee <= 0) {
            $this->addError('retainerClientId', 'Client has no retainer fee set.');
            return;
        }

        $exists = Invoice::where('client_id', $client->id)
            ->where('type', 'retainer')
            ->whereYear('invoice_date', now()->year)
            ->whereMonth('invoice_date', now()->month)
            ->exists();

        if ($exists) {
            $this->addError('retainerClientId', 'Retainer invoice for this month already exists for this client.');
            return;
        }

        $invoice = $invoiceService->createFromRetainer($client, auth()->id());
        $this->showRetainerModal = false;
        $this->retainerClientId = 0;
        $this->dispatch('notify', message: 'Retainer invoice ' . $invoice->invoice_number . ' created.', type: 'success');
    }

    public function resendEmail(int $id): void
    {
        $invoice = Invoice::with('client')->findOrFail($id);
        $email = $invoice->client->pic_email ?? null;
        if (!$email) {
            $this->dispatch('notify', message: 'Klien tidak memiliki alamat email.', type: 'error');
            return;
        }
        $invoice->client->notifyNow(new InvoiceGeneratedNotification($invoice));
        $invoice->logSend('sent', $email);
        $this->dispatch('notify', message: 'Email invoice dikirim ke ' . $email . '.', type: 'success');
    }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteInvoice(): void
    {
        Invoice::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Invoice deleted.', type: 'success');
    }

    public function render()
    {
        $invoices = Invoice::with(['client', 'sendLogs' => fn($q) => $q->latest('sent_at')->limit(1)])
            ->when($this->search, fn($q) => $q->where('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn($q) => $q->where('company_name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_id', $this->clientFilter))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->orderByDesc('invoice_date')
            ->paginate(15);

        $clients = Client::where('is_active', true)->orderBy('company_name')->get();
        $totalUnpaid = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total_amount');

        return view('livewire.invoices.invoice-list', compact('invoices', 'clients', 'totalUnpaid'))
            ->layout('layouts.app', ['title' => 'Invoices']);
    }
}
