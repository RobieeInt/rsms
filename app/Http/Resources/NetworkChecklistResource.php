<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NetworkChecklistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_report_id' => $this->visit_report_id,
            'internet_connectivity' => $this->internet_connectivity,
            'internet_connectivity_notes' => $this->internet_connectivity_notes,
            'speed_test' => $this->speed_test,
            'speed_test_notes' => $this->speed_test_notes,
            'download_speed' => $this->download_speed,
            'upload_speed' => $this->upload_speed,
            'router_check' => $this->router_check,
            'router_check_notes' => $this->router_check_notes,
            'lan_cable_check' => $this->lan_cable_check,
            'lan_cable_check_notes' => $this->lan_cable_check_notes,
            'ip_conflict_check' => $this->ip_conflict_check,
            'ip_conflict_check_notes' => $this->ip_conflict_check_notes,
            'general_notes' => $this->general_notes,
        ];
    }
}
