<?php

namespace App\Livewire\Schedules;

use App\Models\Client;
use App\Models\Schedule;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduleList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public string $technicianFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteSchedule(): void
    {
        Schedule::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Schedule deleted.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();

        $schedules = Schedule::with(['client', 'technician'])
            ->when($user->hasRole('technician'), fn($q) => $q->where('technician_id', $user->id))
            ->when($this->search, fn($q) => $q->whereHas('client', fn($q) => $q->where('company_name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->clientFilter, fn($q) => $q->where('client_id', $this->clientFilter))
            ->when($this->technicianFilter, fn($q) => $q->where('technician_id', $this->technicianFilter))
            ->when($this->dateFrom, fn($q) => $q->where('visit_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('visit_date', '<=', $this->dateTo))
            ->orderByDesc('visit_date')
            ->paginate(15);

        $clients = Client::where('is_active', true)->orderBy('company_name')->get();
        $technicians = User::role('technician')->orderBy('name')->get();

        return view('livewire.schedules.schedule-list', compact('schedules', 'clients', 'technicians'))
            ->layout('layouts.app', ['title' => 'Schedules']);
    }
}
