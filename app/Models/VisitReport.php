<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id', 'client_id', 'technician_id', 'report_number',
        'summary', 'overall_notes', 'technician_signature', 'client_signature',
        'client_signed_by', 'signed_at', 'status',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function assetChecklists()
    {
        return $this->hasMany(AssetChecklist::class);
    }

    public function networkChecklist()
    {
        return $this->hasOne(NetworkChecklist::class);
    }

    public function photos()
    {
        return $this->hasMany(VisitPhoto::class);
    }

    public function findings()
    {
        return $this->hasMany(Finding::class);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        return 'RPT-' . $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getPublicPdfUrl(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'report.pdf.public',
            now()->addDays(30),
            ['report' => $this->id]
        );
    }
}
