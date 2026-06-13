<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'technician_id', 'visit_date', 'start_time', 'end_time',
        'status', 'notes', 'checked_in_at', 'checked_out_at',
        'checkin_lat', 'checkin_lng', 'checkout_lat', 'checkout_lng',
        'checkin_photo', 'checkout_photo',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'checkin_lat' => 'float',
        'checkin_lng' => 'float',
        'checkout_lat' => 'float',
        'checkout_lng' => 'float',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function visitReport()
    {
        return $this->hasOne(VisitReport::class);
    }
}
