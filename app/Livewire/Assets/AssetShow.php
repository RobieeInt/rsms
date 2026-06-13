<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use Livewire\Component;

class AssetShow extends Component
{
    public Asset $asset;

    public function mount(Asset $asset): void
    {
        $this->asset = $asset;
    }

    public function render()
    {
        $this->asset->load(['client', 'checklists.visitReport', 'findings']);

        return view('livewire.assets.asset-show')
            ->layout('layouts.app', ['title' => $this->asset->asset_name]);
    }
}
