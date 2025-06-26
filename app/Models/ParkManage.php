<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ParkManage extends Model
{
    use HasFactory;

    protected $table = 'park_manage';

    protected $fillable = [
        'zone_id',
        'reason',
        'date',
        'start_time',
        'end_time',
        'schedule_type',
        'weekly_days',
        'recurring_start_date',
        'recurring_end_date',
        'is_recurring',
        'is_cancelled',
        'cancelled_at'
    ];

    protected $casts = [
        'weekly_days' => 'array',
        'date' => 'date',
        'recurring_start_date' => 'date',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime'
    ];

    /**
     * Get the parking zone that owns the block.
     */
    public function zone()
    {
        return $this->belongsTo(ParkingZone::class, 'zone_id');
    }

    /**
     * Scope for future blocks
     */
    public function scopeFuture($query)
    {
        return $query->where(function($q) {
            $q->where('date', '>', Carbon::today())
              ->orWhere(function($subQ) {
                  $subQ->where('is_recurring', true)
                       ->where(function($recurQ) {
                           $recurQ->whereNull('recurring_end_date')
                                  ->orWhere('recurring_end_date', '>=', Carbon::today());
                       });
              });
        });
    }

    /**
     * Scope for active blocks
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('date', $now->toDateString())
                    ->where('start_time', '<=', $now->format('H:i:s'))
                    ->where('end_time', '>=', $now->format('H:i:s'));
    }

    /**
     * Scope for recurring blocks
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Get the status of the block
     */
    public function getStatusAttribute()
    {
        $now = Carbon::now();
        $blockDate = Carbon::parse($this->date);
        $blockStart = Carbon::parse($this->date . ' ' . $this->start_time);
        $blockEnd = Carbon::parse($this->date . ' ' . $this->end_time);

        if ($this->is_cancelled) {
            return 'cancelled';
        }

        if ($this->is_recurring) {
            return 'recurring';
        }

        if ($now > $blockEnd) {
            return 'completed';
        } elseif ($now >= $blockStart && $now <= $blockEnd) {
            return 'active';
        } else {
            return 'future';
        }
    }

    /**
     * Get formatted weekly days
     */
    public function getFormattedWeeklyDaysAttribute()
    {
        if (!$this->is_recurring || !$this->weekly_days) {
            return null;
        }

        $days = [
            1 => 'Mon',
            2 => 'Tue', 
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
            7 => 'Sun'
        ];

        return collect($this->weekly_days)
            ->map(fn($day) => $days[$day] ?? '')
            ->filter()
            ->join(', ');
    }

    /**
     * Check if block is active for a specific date
     */
    public function isActiveForDate($date)
    {
        $checkDate = Carbon::parse($date);
        
        if (!$this->is_recurring) {
            return $this->date->isSameDay($checkDate);
        }

        // Check if date is within recurring range
        if ($checkDate->lt($this->recurring_start_date)) {
            return false;
        }

        if ($this->recurring_end_date && $checkDate->gt($this->recurring_end_date)) {
            return false;
        }

        // Check if day of week matches
        $dayOfWeek = $checkDate->dayOfWeek === 0 ? 7 : $checkDate->dayOfWeek;
        return in_array($dayOfWeek, $this->weekly_days ?? []);
    }
}