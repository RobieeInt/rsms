<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'asset_code', 'asset_name', 'asset_type', 'brand', 'model',
        'serial_number', 'cpu', 'ram', 'storage', 'operating_system', 'location',
        'purchase_year', 'notes', 'health_status', 'qr_code',
    ];

    public static function assetTypes(): array
    {
        return [
            'desktop_pc' => 'Desktop PC',
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'router' => 'Router',
            'switch' => 'Switch',
            'access_point' => 'Access Point',
            'cctv' => 'CCTV',
            'nas' => 'NAS',
            'server' => 'Server',
            'other' => 'Other',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function checklists()
    {
        return $this->hasMany(AssetChecklist::class);
    }

    public function findings()
    {
        return $this->hasMany(Finding::class);
    }

    public static function generateCode(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'AST-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function getTypeLabel(): string
    {
        return static::assetTypes()[$this->asset_type] ?? $this->asset_type;
    }
}
