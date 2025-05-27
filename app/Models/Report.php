<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    const STATUS_EMPTY = 1;
    const STATUS_HALF_FULL = 2;
    const STATUS_FULL = 3;

    protected $fillable = ['user_id', 'parking_zone_id', 'status', 'expires_at'];

    protected static function booted()
    {
        static::creating(function ($report) {
            $report->expires_at = now()->addHours(24); // Auto-expire in 2 hours
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
    {
        return $this->belongsTo(ParkingZone::class, 'parking_zone_id');
    }
    public function parkingZone()
    {
        return $this->belongsTo(\App\Models\ParkingZone::class);
    }
}