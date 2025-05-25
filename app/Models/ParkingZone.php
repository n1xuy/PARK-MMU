<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingZone extends Model
{
    protected $fillable = [
        'name', 
        'status',
        'zone_number', // Add this
        'location',
        'last_reported_at',
        'total_reports'
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