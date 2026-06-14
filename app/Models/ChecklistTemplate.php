<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'asset_type', 'items', 'is_active'];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
    ];

    public static function forAssetType(string $assetType): ?self
    {
        return static::where('asset_type', $assetType)->where('is_active', true)->first()
            ?? static::whereNull('asset_type')->where('is_active', true)->first();
    }

    public function defaultResults(): array
    {
        $results = [];
        foreach ($this->items as $item) {
            $results[$item['key']] = ['result' => 'na', 'notes' => ''];
        }
        return $results;
    }
}
