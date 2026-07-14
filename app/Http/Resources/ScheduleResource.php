<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'technician_id' => $this->technician_id,
            'technician' => new UserResource($this->whenLoaded('technician')),
            'visit_date' => $this->visit_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'notes' => $this->notes,
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'checked_out_at' => $this->checked_out_at?->toIso8601String(),
            'checkin_lat' => $this->checkin_lat,
            'checkin_lng' => $this->checkin_lng,
            'checkout_lat' => $this->checkout_lat,
            'checkout_lng' => $this->checkout_lng,
            'checkin_photo' => $this->checkin_photo,
            'checkout_photo' => $this->checkout_photo,
            'visit_report' => new VisitReportResource($this->whenLoaded('visitReport')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
