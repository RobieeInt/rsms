<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Invoice yang udah lunas dikunci (nggak bisa diedit/dihapus) biar
     * laporan pembayaran nggak berubah. Selain itu (draft/sent/overdue)
     * bebas diedit atau dihapus.
     */
    public function isLocked(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Posisi invoice ini terhadap total penawarannya — buat nampilin sisa
     * pembayaran di invoice termin. "Sebelumnya" = invoice lain (non-batal)
     * dari penawaran yang sama yang dibuat sebelum invoice ini, jadi angka di
     * invoice lama nggak berubah walau termin berikutnya udah dibuat.
     * Null kalau bukan invoice termin (invoice penuh / manual / retainer).
     *
     * @return array{quotation_number: string, quotation_total: float, billed_before: float, this_invoice: float, remaining_after: float, this_percent: float, billed_before_percent: float, remaining_after_percent: float, paid_total: float, outstanding: float}|null
     */
    public function quotationSummary(): ?array
    {
        if (! $this->quotation_id || ! $this->installment_number || ! $this->quotation) {
            return null;
        }

        $siblings = $this->quotation->billedInvoices();
        $before = $siblings->filter(fn ($inv) => $inv->id < $this->id);
        $quotationTotal = (float) $this->quotation->total_amount;
        $billedBefore = round((float) $before->sum(fn ($inv) => (float) $inv->total_amount), 2);
        $thisInvoice = $this->status === 'cancelled' ? 0.0 : (float) $this->total_amount;
        $paidTotal = round((float) $siblings->where('status', 'paid')->sum(fn ($inv) => (float) $inv->total_amount), 2);

        $pct = fn (float $amount) => $quotationTotal > 0 ? round($amount / $quotationTotal * 100, 2) : 0.0;
        $remainingAfter = max(0, round($quotationTotal - $billedBefore - $thisInvoice, 2));

        return [
            'quotation_number' => $this->quotation->quotation_number,
            'quotation_total' => $quotationTotal,
            'billed_before' => $billedBefore,
            'this_invoice' => $thisInvoice,
            'remaining_after' => $remainingAfter,
            // Persen dari total penawaran (nominal aktual, bukan skema — jadi
            // termin yang diturunin/dinaikin tetap nampil sesuai kenyataan).
            'this_percent' => $pct($thisInvoice),
            'billed_before_percent' => $pct($billedBefore),
            'remaining_after_percent' => $pct($remainingAfter),
            'paid_total' => $paidTotal,
            'outstanding' => max(0, round($quotationTotal - $paidTotal, 2)),
        ];
    }

    protected $fillable = [
        'client_id', 'quotation_id', 'installment_number', 'created_by', 'invoice_number', 'type',
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

    /** Internal cost documentation — never shown to the client. */
    public function totalCost(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->cost_price !== null ? $item->cost_price * $item->quantity : 0);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = 'INV-' . $year . $month . '-';
        // Include soft-deleted invoices: their numbers are still taken by the
        // unique index, and counting only live rows re-issues a used number
        // right after any invoice is deleted.
        $last = static::withTrashed()->where('invoice_number', 'like', $prefix . '%')->max('invoice_number');
        $seq = $last ? (int) substr($last, strlen($prefix)) : 0;
        return $prefix . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function getPublicPdfUrl(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'invoice.pdf.public',
            now()->addDays(7),
            ['invoice' => $this->id]
        );
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
        $pdfUrl = $this->getPublicPdfUrl();

        $itemList = $this->items->map(fn($item) => "- *{$item->description}*")->implode("\n");

        $text = "Halo {$name},\n\n"
            . "Berikut kami sampaikan invoice dari *Reconext Digital Kreasi*:\n\n"
            . "- *No. Invoice:* {$this->invoice_number}\n";

        if ($itemList) {
            $text .= "{$itemList}\n";
        }

        $text .= "- *Jumlah:* {$amount}\n"
            . "- *Jatuh Tempo:* {$due}\n\n"
            . "Silakan unduh PDF invoice di tautan berikut (berlaku 7 hari):\n"
            . "{$pdfUrl}\n\n"
            . "Mohon melakukan pembayaran sebelum tanggal jatuh tempo.\n"
            . "Terima kasih 🙏\n\n"
            . "_Reconext Digital Kreasi_";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }
}
