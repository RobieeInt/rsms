<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Services\InvoiceService;
use Livewire\Component;
use Livewire\WithPagination;

class ClientShow extends Component
{
    use WithPagination;

    public Client $client;
    public string $activeTab = 'assets';

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function generateRetainerInvoice(InvoiceService $invoiceService): void
    {
        if ($this->client->monthly_retainer_fee <= 0) {
            $this->dispatch('notify', message: 'Client has no retainer fee set.', type: 'error');
            return;
        }

        $exists = \App\Models\Invoice::where('client_id', $this->client->id)
            ->where('type', 'retainer')
            ->whereYear('invoice_date', now()->year)
            ->whereMonth('invoice_date', now()->month)
            ->exists();

        if ($exists) {
            $this->dispatch('notify', message: 'Retainer invoice for this month already exists.', type: 'warning');
            return;
        }

        $invoice = $invoiceService->createFromRetainer($this->client, auth()->id());
        $this->dispatch('notify', message: 'Retainer invoice ' . $invoice->invoice_number . ' created.', type: 'success');
        $this->activeTab = 'invoices';
        $this->resetPage();
    }

    public function render()
    {
        $stats = [
            'assets' => $this->client->assets()->count(),
            'visits' => $this->client->schedules()->count(),
            'open_findings' => $this->client->findings()->whereNotIn('status', ['resolved'])->count(),
            'unpaid_invoices' => $this->client->invoices()->whereNotIn('status', ['paid', 'cancelled'])->count(),
        ];

        $assets = null;
        $schedules = null;
        $findings = null;
        $invoices = null;

        if ($this->activeTab === 'assets') {
            $assets = $this->client->assets()->latest()->paginate(10);
        } elseif ($this->activeTab === 'schedules') {
            $schedules = $this->client->schedules()->with('technician')->latest('visit_date')->paginate(10);
        } elseif ($this->activeTab === 'findings') {
            $findings = $this->client->findings()->with('asset')->latest()->paginate(10);
        } elseif ($this->activeTab === 'invoices') {
            $invoices = $this->client->invoices()->latest('invoice_date')->paginate(10);
        }

        return view('livewire.clients.client-show', compact('stats', 'assets', 'schedules', 'findings', 'invoices'))
            ->layout('layouts.app', ['title' => $this->client->company_name]);
    }
}
