<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'created_by', 'quotation_number', 'date', 'expiry_date',
        'subtotal', 'tax_percent', 'tax_amount', 'discount_amount', 'total_amount',
        'notes', 'status', 'approval_token', 'approved_at', 'approved_by_name', 'approval_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        return 'QUO-' . $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function getApprovalUrl(): string
    {
        return route('quotation.approve', ['token' => $this->approval_token]);
    }

    public function getPublicPdfUrl(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'quotation.pdf.public',
            now()->addDays(30),
            ['quotation' => $this->id]
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
        $amount = 'Rp ' . number_format($this->total_amount, 0, ',', '.');
        $pdfUrl = $this->getPublicPdfUrl();
        $approvalUrl = $this->getApprovalUrl();

        $itemList = $this->items->map(fn($item) => "- *{$item->description}*")->implode("\n");

        $text = "Halo {$name},\n\n"
            . "Berikut kami sampaikan penawaran dari *Reconext Digital Kreasi*:\n\n"
            . "- *No. Quotation:* {$this->quotation_number}\n";

        if ($itemList) {
            $text .= "{$itemList}\n";
        }

        $text .= "- *Nilai:* {$amount}\n"
            . "- *Berlaku hingga:* {$this->expiry_date->locale('id')->translatedFormat('d F Y')}\n\n"
            . "Silakan unduh PDF penawaran di tautan berikut (berlaku 30 hari):\n"
            . "{$pdfUrl}\n\n"
            . "Untuk menyetujui atau menolak penawaran, silakan klik tautan berikut:\n"
            . "{$approvalUrl}\n\n"
            . "Terima kasih 🙏\n\n"
            . "_Reconext Digital Kreasi_";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }
}
