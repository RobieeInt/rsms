<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\URL;

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

    public function getWhatsappUrl(): ?string
    {
        $phone = $this->client->pic_phone ?? null;
        if (!$phone) return null;

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        Carbon::setLocale('id');
        $name   = $this->client->pic_name ?? 'Bapak/Ibu';
        $date   = $this->schedule->visit_date->locale('id')->translatedFormat('d F Y');
        $pdfUrl = $this->getPublicPdfUrl();

        $text = "Halo {$name},\n\n"
            . "Berikut kami sampaikan laporan kunjungan dari *Reconext Digital Kreasi*:\n\n"
            . "📄 *No. Laporan:* {$this->report_number}\n"
            . "📅 *Tanggal Kunjungan:* {$date}\n\n"
            . "Silakan unduh PDF laporan di tautan berikut (berlaku 30 hari):\n"
            . "{$pdfUrl}\n\n"
            . "Terima kasih 🙏\n\n"
            . "_Reconext Digital Kreasi_";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }
}
