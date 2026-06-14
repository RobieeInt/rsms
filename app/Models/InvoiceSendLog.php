<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSendLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['invoice_id', 'type', 'sent_to', 'channel', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'sent'             => 'Invoice dikirim',
            'reminder_7days'   => 'Reminder 7 hari sebelum jatuh tempo',
            'reminder_3days'   => 'Reminder 3 hari sebelum jatuh tempo',
            'due_today'        => 'Reminder hari jatuh tempo',
            'overdue_7days'    => 'Reminder 7 hari setelah jatuh tempo',
            default            => $this->type,
        };
    }
}
