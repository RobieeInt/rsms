<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $healthFilter = '';
    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'healthFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingHealthFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->showDeleteModal = false;
    }

    public function deleteClient(): void
    {
        $client = Client::findOrFail($this->deleteId);
        $client->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Client deleted successfully.');
        $this->dispatch('notify', type: 'success', message: 'Client deleted successfully.');
    }

    public function export(): void
    {
        // Export logic placeholder - implement with CSV/Excel export
        $this->dispatch('notify', type: 'info', message: 'Export feature coming soon.');
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                  ->orWhere('pic_name', 'like', "%{$this->search}%")
                  ->orWhere('pic_email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->when($this->healthFilter, fn($q) => $q->where('health_status', $this->healthFilter))
            ->orderBy('company_name')
            ->paginate(15);

        return view('livewire.clients.client-list', compact('clients'))
            ->layout('layouts.app', ['title' => 'Clients']);
    }
}
