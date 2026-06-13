<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class AssetList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $clientFilter = '';
    public string $typeFilter = '';
    public string $healthFilter = '';
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingClientFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteAsset(): void
    {
        Asset::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Asset deleted.', type: 'success');
    }

    public function render()
    {
        $assets = Asset::with('client')
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('asset_name', 'like', "%{$this->search}%")
                  ->orWhere('asset_code', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%");
            }))
            ->when($this->clientFilter, fn($q) => $q->where('client_id', $this->clientFilter))
            ->when($this->typeFilter, fn($q) => $q->where('asset_type', $this->typeFilter))
            ->when($this->healthFilter, fn($q) => $q->where('health_status', $this->healthFilter))
            ->orderBy('asset_code')
            ->paginate(15);

        $clients = Client::where('is_active', true)->orderBy('company_name')->get();
        $assetTypes = Asset::assetTypes();

        return view('livewire.assets.asset-list', compact('assets', 'clients', 'assetTypes'))
            ->layout('layouts.app', ['title' => 'Assets']);
    }
}
