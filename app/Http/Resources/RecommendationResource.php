<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'finding_id' => $this->finding_id,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'recommendation' => $this->recommendation,
            'priority' => $this->priority,
            'is_quoted' => $this->is_quoted,
            'created_at' => $this->created_at,
        ];
    }
}
