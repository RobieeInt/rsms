<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkChecklist extends Model
{
    protected $fillable = [
        'visit_report_id',
        'internet_connectivity', 'internet_connectivity_notes',
        'speed_test', 'speed_test_notes', 'download_speed', 'upload_speed',
        'router_check', 'router_check_notes',
        'lan_cable_check', 'lan_cable_check_notes',
        'ip_conflict_check', 'ip_conflict_check_notes',
        'general_notes',
    ];

    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }
}
