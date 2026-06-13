<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitPhoto extends Model
{
    protected $fillable = [
        'visit_report_id', 'uploaded_by', 'file_path', 'original_name', 'photo_type', 'caption',
    ];

    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
