<?php

namespace App\Livewire\Quotations;

use App\Models\Client;
use App\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteQuotation(): void
    {
        Quotation::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Quotation deleted.', type: 'success');
    }

    public function render()
    {
        $quotations = Quotation::with('client')
            ->when($this->search, fn($q) => $q->where('quotation_number', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn($q) => $q->where('company_name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_id', $this->clientFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        $clients = Client::where('is_active', true)->orderBy('company_name')->get();

        return view('livewire.quotations.quotation-list', compact('quotations', 'clients'))
            ->layout('layouts.app', ['title' => 'Quotations']);
    }
}
