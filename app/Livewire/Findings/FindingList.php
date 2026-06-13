<?php

namespace App\Livewire\Findings;

use App\Models\Client;
use App\Models\Finding;
use Livewire\Component;
use Livewire\WithPagination;

class FindingList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $severityFilter = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteFinding(): void
    {
        Finding::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Finding deleted.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();

        $findings = Finding::with(['client', 'asset', 'reporter'])
            ->when($user->hasRole('technician'), fn($q) => $q->where('reported_by', $user->id))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->severityFilter, fn($q) => $q->where('severity', $this->severityFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_id', $this->clientFilter))
            ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low')")
            ->orderByDesc('created_at')
            ->paginate(15);

        $clients = Client::where('is_active', true)->orderBy('company_name')->get();

        return view('livewire.findings.finding-list', compact('findings', 'clients'))
            ->layout('layouts.app', ['title' => 'Findings']);
    }
}
