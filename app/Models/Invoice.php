<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'quotation_id', 'created_by', 'invoice_number', 'type',
        'invoice_date', 'due_date', 'subtotal', 'tax_percent', 'tax_amount',
        'discount_amount', 'total_amount', 'notes', 'status',
        'payment_date', 'payment_method', 'payment_proof', 'payment_notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
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

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function sendLogs()
    {
        return $this->hasMany(InvoiceSendLog::class)->orderByDesc('sent_at');
    }

    public function logSend(string $type, ?string $sentTo = null, string $channel = 'email'): void
    {
        $this->sendLogs()->create(['type' => $type, 'sent_to' => $sentTo, 'channel' => $channel]);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $last = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        return 'INV-' . $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function getWhatsappUrl(): ?string
    {
        $phone = $this->client->pic_phone ?? null;
        if (!$phone) return null;

        // Normalize to Indonesian international format (62xxx)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        Carbon::setLocale('id');
        $name   = $this->client->pic_name ?? 'Bapak/Ibu';
        $amount = 'Rp ' . number_format($this->total_amount, 0, ',', '.');
        $due    = $this->due_date->locale('id')->translatedFormat('d F Y');
        $pdfUrl = route('pdf.invoice', $this->id);

        $text = "Halo {$name},\n\n"
            . "Berikut kami sampaikan invoice dari *Reconext Digital Kreasi*:\n\n"
            . "📄 *No. Invoice:* {$this->invoice_number}\n"
            . "💰 *Jumlah:* {$amount}\n"
            . "📅 *Jatuh Tempo:* {$due}\n\n"
            . "Silakan unduh PDF invoice di tautan berikut:\n"
            . "{$pdfUrl}\n\n"
            . "Mohon melakukan pembayaran sebelum tanggal jatuh tempo.\n"
            . "Terima kasih 🙏\n\n"
            . "_Reconext Digital Kreasi_";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }
}
