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
        'notes', 'payment_terms', 'status', 'approval_token', 'approved_at', 'approved_by_name', 'approval_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'payment_terms' => 'array',
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

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->orderBy('created_at');
    }

    /** Sum of total_amount for all non-cancelled invoices billed against this quotation. */
    public function totalInvoiced(): float
    {
        $invoices = $this->relationLoaded('invoices') ? $this->invoices : $this->invoices()->get();

        return (float) $invoices->where('status', '!=', 'cancelled')->sum(fn ($inv) => (float) $inv->total_amount);
    }

    public function remainingBalance(): float
    {
        return round((float) $this->total_amount - $this->totalInvoiced(), 2);
    }

    /** 30 -> "30", 33.5 -> "33,5" */
    public static function formatPercent(float $percent): string
    {
        return rtrim(rtrim(number_format($percent, 2, ',', '.'), '0'), ',');
    }

    public function billedInvoices()
    {
        $invoices = $this->relationLoaded('invoices') ? $this->invoices : $this->invoices()->get();

        return $invoices->where('status', '!=', 'cancelled')->values();
    }

    /**
     * Agreed payment schedule, each term with its nominal computed from the
     * quotation total. The last term absorbs rounding so the schedule always
     * sums to exactly total_amount. Empty = pay in full at once.
     *
     * @return array<int, array{label: string, percent: float, amount: float}>
     */
    public function scheduledTerms(): array
    {
        $terms = array_values(array_filter($this->payment_terms ?? [], fn ($t) => (float) ($t['percent'] ?? 0) > 0));
        $total = (float) $this->total_amount;
        $allocated = 0.0;

        foreach ($terms as $i => $term) {
            $amount = $i === count($terms) - 1
                ? round($total - $allocated, 2)
                : round($total * (float) $term['percent'] / 100, 2);
            $allocated += $amount;
            $terms[$i] = [
                'label' => trim((string) ($term['label'] ?? '')) ?: 'Termin ' . ($i + 1),
                'percent' => (float) $term['percent'],
                'amount' => $amount,
            ];
        }

        return $terms;
    }

    /**
     * What the next invoice should bill by default: the next term in the
     * agreed schedule (capped at what's actually left, since earlier
     * installments may have been billed higher/lower than agreed), or the
     * full remaining balance when there's no schedule / it's used up.
     *
     * @return array{label: ?string, amount: float, number: int, scheduled: bool}
     */
    public function nextTerm(): array
    {
        $remaining = $this->remainingBalance();
        $number = $this->billedInvoices()->count() + 1;
        $term = $this->scheduledTerms()[$number - 1] ?? null;
        $isLastScheduled = $term && $number === count($this->scheduledTerms());

        return [
            'label' => $term['label'] ?? null,
            'amount' => max(0, $term && ! $isLastScheduled ? min($term['amount'], $remaining) : $remaining),
            'number' => $number,
            'scheduled' => (bool) $term,
        ];
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
        $prefix = 'QUO-' . $year . $month . '-';
        // Include soft-deleted rows so a deleted quotation's number is never re-issued.
        $last = static::withTrashed()->where('quotation_number', 'like', $prefix . '%')->max('quotation_number');
        $seq = $last ? (int) substr($last, strlen($prefix)) : 0;
        return $prefix . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
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
