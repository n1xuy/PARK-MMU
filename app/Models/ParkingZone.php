<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingZone extends Model
{
    protected $fillable = [
        'name',
        'status',
        'last_reported_at',
        'total_reports',
        'zone_number',
        'location',
        'total_empty',
        'total_half_full',
        'total_full',
        'reliable_status',
        'reliable_status_updated_at',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getCurrentStatusAttribute()
    {
        return $this->reports()
            ->where('created_at', '>=', now()->subHours(2))
            ->latest()
            ->first()?->status ?? 'empty';
    }

    public function updateStats()
    {
        $this->update([
            'total_reports' => $this->reports()->count(),
            'last_reported_at' => now()
        ]);
    }

}