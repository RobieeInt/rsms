<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'asset_code' => $this->asset_code,
            'asset_name' => $this->asset_name,
            'asset_type' => $this->asset_type,
            'type_label' => $this->getTypeLabel(),
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'cpu' => $this->cpu,
            'ram' => $this->ram,
            'storage' => $this->storage,
            'operating_system' => $this->operating_system,
            'location' => $this->location,
            'purchase_year' => $this->purchase_year,
            'notes' => $this->notes,
            'health_status' => $this->health_status,
            'qr_code' => $this->qr_code,
            'checklists' => AssetChecklistResource::collection($this->whenLoaded('checklists')),
            'findings' => FindingResource::collection($this->whenLoaded('findings')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
