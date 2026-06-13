<?php

namespace App\Livewire\Technicians;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TechnicianList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteTechnician(): void
    {
        $user = User::findOrFail($this->deleteId);
        $user->delete();
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Technician deleted.', type: 'success');
    }

    public function render()
    {
        $technicians = User::role('technician')
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter === '1'))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.technicians.technician-list', compact('technicians'))
            ->layout('layouts.app', ['title' => 'Technicians']);
    }
}
