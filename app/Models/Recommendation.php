<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'finding_id', 'created_by', 'recommendation', 'priority', 'is_quoted',
    ];

    protected $casts = [
        'is_quoted' => 'boolean',
    ];

    public function finding()
    {
        return $this->belongsTo(Finding::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
