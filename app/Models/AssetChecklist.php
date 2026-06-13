<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetChecklist extends Model
{
    protected $fillable = [
        'visit_report_id', 'asset_id',
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

    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function getChecklistItems(): array
    {
        return [
            'storage_check' => 'Storage Check',
            'ram_check' => 'RAM Usage Check',
            'temp_files_cleanup' => 'Temporary Files Cleanup',
            'ssd_health_check' => 'SSD Health Check',
            'windows_update_check' => 'Windows Update Check',
            'driver_check' => 'Driver Check',
            'virus_scan' => 'Virus Scan',
            'printer_check' => 'Printer Check',
            'hardware_cleaning' => 'Hardware Cleaning',
        ];
    }
}
