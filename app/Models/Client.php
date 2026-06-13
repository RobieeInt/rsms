<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    public function routeNotificationForMail(): string
    {
        return $this->pic_email ?? '';
    }

    protected $fillable = [
        'company_name', 'pic_name', 'pic_email', 'pic_phone', 'address',
        'monthly_retainer_fee', 'invoice_due_date', 'is_active', 'notes',
        'health_score', 'health_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monthly_retainer_fee' => 'decimal:2',
        'health_score' => 'decimal:2',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function visitReports()
    {
        return $this->hasMany(VisitReport::class);
    }

    public function findings()
    {
        return $this->hasMany(Finding::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function recalculateHealthScore(): void
    {
        $openFindings = $this->findings()->where('status', '!=', 'resolved')->count();
        $criticalFindings = $this->findings()->where('severity', 'critical')->where('status', '!=', 'resolved')->count();
        $highFindings = $this->findings()->where('severity', 'high')->where('status', '!=', 'resolved')->count();

        $score = 100;
        $score -= $criticalFindings * 20;
        $score -= $highFindings * 10;
        $score -= ($openFindings - $criticalFindings - $highFindings) * 5;
        $score = max(0, $score);

        $status = match (true) {
            $score >= 80 => 'healthy',
            $score >= 50 => 'warning',
            default => 'critical',
        };

        $this->update(['health_score' => $score, 'health_status' => $status]);
    }
}
