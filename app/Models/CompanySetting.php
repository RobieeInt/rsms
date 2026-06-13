<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name', 'logo', 'email', 'phone', 'address',
        'bank_name', 'bank_account_number', 'bank_account_holder', 'website', 'tax_number',
    ];

    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'Reconext Digital Kreasi',
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }
}
