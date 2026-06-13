<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Finding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'asset_id', 'visit_report_id', 'reported_by',
        'title', 'description', 'category', 'severity', 'status', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function getSeverityBadgeClass(): string
    {
        return match ($this->severity) {
            'critical' => 'badge-critical',
            'high' => 'badge-high',
            'medium' => 'badge-medium',
            'low' => 'badge-low',
            default => 'badge-low',
        };
    }
}
