<?php

namespace App\Models;

use Carbon\Carbon;
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
        'zone_type',
        'location',
        'total_empty',
        'total_half_full',
        'total_full',
        'reliable_status',
        'is_blocked',
        'block_reason',
        'block_date',
        'block_start_time',
        'block_end_time',
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

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'full' => '#F44336',
            'half_full' => '#FF9800',
            'blocked' => '#000000',
            default => '#4CAF50',
        };
    }


    public function updateStats()
    {
        $this->update([ 
            'total_reports' => $this->reports()->count(),
            'last_reported_at' => now()
        ]);
    }

    public function isBlocked()
    {
        // If not marked as blocked, return false
        if (!$this->is_blocked) {
            return false;
        }

        if ($this->block_expires_at && now()->greaterThan($this->block_expires_at)) {
        $this->update([
            'is_blocked' => false,
            'block_reason' => null,
            'block_date' => null,  
            'block_start_time' => null,
            'block_end_time' => null,
            'block_expires_at' => null,
        ]);
        return false;
    }

        // If no date/time specified, consider it blocked
        if (!$this->block_date || !$this->block_start_time || !$this->block_end_time) {
            return true;
        }

        $now = Carbon::now();
        $blockDate = Carbon::parse($this->block_date);
        $blockStart = Carbon::parse($this->block_date . ' ' . $this->block_start_time);
        $blockEnd = Carbon::parse($this->block_date . ' ' . $this->block_end_time);

        // Handle overnight blocks (end time is next day)
        if ($this->block_end_time < $this->block_start_time) {
            $blockEnd->addDay();
        }

        // Check if current time is within block period
        return $now->between($blockStart, $blockEnd);

    }

    public function parkManages()
    {
        return $this->hasMany(\App\Models\ParkManage::class, 'zone_id');
    }

    /**
     * Get the next future block (not active now, not cancelled) for this zone
     */
    public function nextFutureBlock()
    {
        $now = now();
        $today = $now->toDateString();

        // Single future block
        $single = \App\Models\ParkManage::where('zone_id', $this->id)
            ->where('is_cancelled', false)
            ->where('is_recurring', false)
            ->where(function($q) use ($now) {
                $q->where('date', '>', $now->toDateString())
                ->orWhere(function($q2) use ($now) {
                    $q2->where('date', $now->toDateString())
                        ->where('start_time', '>', $now->format('H:i:s'));
                });
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // Recurring future block: find the next actual date
        $recurring = \App\Models\ParkManage::where('zone_id', $this->id)
            ->where('is_cancelled', false)
            ->where('is_recurring', true)
            ->where(function($q) use ($today) {
                $q->whereNull('recurring_end_date')
                ->orWhere('recurring_end_date', '>=', $today);
            })
            ->orderBy('recurring_start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $nextRecurring = null;
        $nextRecurringDate = null;

        foreach ($recurring as $block) {
            $start = $block->recurring_start_date ?? $today;
            $end = $block->recurring_end_date ?? $today;
            $days = json_decode($block->weekly_days, true) ?: [];

            // Find the next matching day from today
            $date = Carbon::parse($today);
            $limit = Carbon::parse($end);
            while ($date->lte($limit)) {
                if (in_array($date->dayOfWeekIso, $days) && $date->gte($start)) {
                    $nextRecurring = $block;
                    $nextRecurringDate = $date->toDateString();
                    break;
                }
                $date->addDay();
            }
            if ($nextRecurring) break;
        }

        // Compare single and recurring, return the soonest
        if ($single && $nextRecurring && $nextRecurringDate) {
            return (strtotime($single->date) <= strtotime($nextRecurringDate)) ? $single : (object)array_merge($nextRecurring->toArray(), ['date' => $nextRecurringDate]);
        }
        if ($single) return $single;
        if ($nextRecurring && $nextRecurringDate) return (object)array_merge($nextRecurring->toArray(), ['date' => $nextRecurringDate]);
        return null;;
        }
}