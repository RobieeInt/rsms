<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Client;
use Livewire\Component;

class AssetForm extends Component
{
    public ?Asset $asset = null;
    public ?Client $client = null;

    public int $client_id = 0;
    public string $asset_name = '';
    public string $asset_type = 'desktop_pc';
    public string $brand = '';
    public string $model = '';
    public string $serial_number = '';
    public string $cpu = '';
    public string $ram = '';
    public string $storage = '';
    public string $operating_system = '';
    public string $location = '';
    public string $purchase_year = '';
    public string $notes = '';
    public string $health_status = 'good';

    public function mount(?Asset $asset = null, ?Client $client = null): void
    {
        if ($client && $client->exists) {
            $this->client = $client;
            $this->client_id = $client->id;
        } elseif ($cid = request('client_id')) {
            $preselected = Client::find((int) $cid);
            if ($preselected) {
                $this->client = $preselected;
                $this->client_id = $preselected->id;
            }
        }

        if ($asset && $asset->exists) {
            $this->asset = $asset;
            $this->client_id = $asset->client_id;
            $this->asset_name = $asset->asset_name;
            $this->asset_type = $asset->asset_type;
            $this->brand = $asset->brand ?? '';
            $this->model = $asset->model ?? '';
            $this->serial_number = $asset->serial_number ?? '';
            $this->cpu = $asset->cpu ?? '';
            $this->ram = $asset->ram ?? '';
            $this->storage = $asset->storage ?? '';
            $this->operating_system = $asset->operating_system ?? '';
            $this->location = $asset->location ?? '';
            $this->purchase_year = $asset->purchase_year ?? '';
            $this->notes = $asset->notes ?? '';
            $this->health_status = $asset->health_status;
        }
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'asset_name' => 'required|string|max:255',
            'asset_type' => 'required|in:' . implode(',', array_keys(Asset::assetTypes())),
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_year' => 'nullable|digits:4|integer',
        ]);

        $data = [
            'client_id' => $this->client_id,
            'asset_name' => $this->asset_name,
            'asset_type' => $this->asset_type,
            'brand' => $this->brand ?: null,
            'model' => $this->model ?: null,
            'serial_number' => $this->serial_number ?: null,
            'cpu' => $this->cpu ?: null,
            'ram' => $this->ram ?: null,
            'storage' => $this->storage ?: null,
            'operating_system' => $this->operating_system ?: null,
            'location' => $this->location ?: null,
            'purchase_year' => $this->purchase_year ?: null,
            'notes' => $this->notes ?: null,
            'health_status' => $this->health_status,
        ];

        if ($this->asset && $this->asset->exists) {
            $this->asset->update($data);
        } else {
            $data['asset_code'] = Asset::generateCode();
            Asset::create($data);
        }

        session()->flash('success', 'Asset saved successfully.');
        $this->redirect(route('assets.index'));
    }

    public function render()
    {
        $isEdit = $this->asset && $this->asset->exists;
        $clients = Client::where('is_active', true)->orderBy('company_name')->get();
        $assetTypes = Asset::assetTypes();

        return view('livewire.assets.asset-form', compact('isEdit', 'clients', 'assetTypes'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Asset' : 'Add Asset']);
    }
}
