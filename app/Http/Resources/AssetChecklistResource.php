<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetChecklistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_report_id' => $this->visit_report_id,
            'asset_id' => $this->asset_id,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'template_id' => $this->template_id,
            'template' => new ChecklistTemplateResource($this->whenLoaded('template')),
            'results' => $this->results,
            'is_template_based' => $this->isTemplateBased(),
            'resolved_items' => $this->getResolvedItems(),
            'storage_check' => $this->storage_check,
            'storage_check_notes' => $this->storage_check_notes,
            'ram_check' => $this->ram_check,
            'ram_check_notes' => $this->ram_check_notes,
            'temp_files_cleanup' => $this->temp_files_cleanup,
            'temp_files_cleanup_notes' => $this->temp_files_cleanup_notes,
            'ssd_health_check' => $this->ssd_health_check,
            'ssd_health_check_notes' => $this->ssd_health_check_notes,
            'windows_update_check' => $this->windows_update_check,
            'windows_update_check_notes' => $this->windows_update_check_notes,
            'driver_check' => $this->driver_check,
            'driver_check_notes' => $this->driver_check_notes,
            'virus_scan' => $this->virus_scan,
            'virus_scan_notes' => $this->virus_scan_notes,
            'printer_check' => $this->printer_check,
            'printer_check_notes' => $this->printer_check_notes,
            'hardware_cleaning' => $this->hardware_cleaning,
            'hardware_cleaning_notes' => $this->hardware_cleaning_notes,
            'general_notes' => $this->general_notes,
            'created_at' => $this->created_at,
        ];
    }
}
