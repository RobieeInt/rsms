<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleSendLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['schedule_id', 'type', 'sent_to', 'channel', 'status', 'error_message', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'created'   => 'Jadwal dibuat',
            'updated'   => 'Jadwal diperbarui',
            'cancelled' => 'Jadwal dibatalkan',
            default     => $this->type,
        };
    }
}
