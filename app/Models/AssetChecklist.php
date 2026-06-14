<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetChecklist extends Model
{
    protected $fillable = [
        'visit_report_id', 'asset_id', 'template_id', 'results',
        'storage_check', 'storage_check_notes',
        'ram_check', 'ram_check_notes',
        'temp_files_cleanup', 'temp_files_cleanup_notes',
        'ssd_health_check', 'ssd_health_check_notes',
        'windows_update_check', 'windows_update_check_notes',
        'driver_check', 'driver_check_notes',
        'virus_scan', 'virus_scan_notes',
        'printer_check', 'printer_check_notes',
        'hardware_cleaning', 'hardware_cleaning_notes',
        'general_notes',
    ];

    protected $casts = [
        'results' => 'array',
    ];

    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function isTemplateBased(): bool
    {
        return $this->template_id !== null && $this->results !== null;
    }

    // Returns [{key, label, result, notes}] for display/PDF
    public function getResolvedItems(): array
    {
        if ($this->isTemplateBased() && $this->template) {
            return array_map(function ($item) {
                $r = $this->results[$item['key']] ?? ['result' => 'na', 'notes' => ''];
                return [
                    'key'    => $item['key'],
                    'label'  => $item['label'],
                    'result' => $r['result'] ?? 'na',
                    'notes'  => $r['notes'] ?? '',
                ];
            }, $this->template->items);
        }

        // Legacy hardcoded fallback
        $legacy = [
            'storage_check'       => 'Storage Check',
            'ram_check'           => 'RAM Usage',
            'temp_files_cleanup'  => 'Temp Files Cleanup',
            'ssd_health_check'    => 'SSD Health',
            'windows_update_check'=> 'Windows Update',
            'driver_check'        => 'Driver Check',
            'virus_scan'          => 'Virus Scan',
            'printer_check'       => 'Printer Check',
            'hardware_cleaning'   => 'Hardware Cleaning',
        ];
        $items = [];
        foreach ($legacy as $field => $label) {
            $v = $this->$field;
            if ($v && $v !== 'na') {
                $items[] = [
                    'key'    => $field,
                    'label'  => $label,
                    'result' => $v,
                    'notes'  => $this->{$field . '_notes'} ?? '',
                ];
            }
        }
        return $items;
    }
}
