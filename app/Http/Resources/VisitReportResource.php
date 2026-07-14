<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'schedule_id' => $this->schedule_id,
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'technician_id' => $this->technician_id,
            'technician' => new UserResource($this->whenLoaded('technician')),
            'report_number' => $this->report_number,
            'summary' => $this->summary,
            'overall_notes' => $this->overall_notes,
            'technician_signature' => $this->technician_signature,
            'client_signature' => $this->client_signature,
            'client_signed_by' => $this->client_signed_by,
            'signed_at' => $this->signed_at?->toIso8601String(),
            'status' => $this->status,
            'asset_checklists' => AssetChecklistResource::collection($this->whenLoaded('assetChecklists')),
            'network_checklist' => new NetworkChecklistResource($this->whenLoaded('networkChecklist')),
            'photos' => VisitPhotoResource::collection($this->whenLoaded('photos')),
            'findings' => FindingResource::collection($this->whenLoaded('findings')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
