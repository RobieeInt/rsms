<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
