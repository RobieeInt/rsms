<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FindingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'asset_id' => $this->asset_id,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'visit_report_id' => $this->visit_report_id,
            'reported_by' => $this->reported_by,
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'severity' => $this->severity,
            'status' => $this->status,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'recommendations' => RecommendationResource::collection($this->whenLoaded('recommendations')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
