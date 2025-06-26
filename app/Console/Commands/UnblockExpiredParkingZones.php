<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ParkingZone;
use Carbon\Carbon;

class UnblockExpiredParkingZones extends Command
{
    protected $signature = 'parking:unblock-expired';
    protected $description = 'Automatically unblock zones with expired block times';

    public function handle()
    {
        $now = Carbon::now();
        
        ParkingZone::where('is_blocked', true)
            ->where(function($query) use ($now) {
                $query->where('block_date', '<', $now->toDateString())
                    ->orWhere(function($q) use ($now) {
                        $q->where('block_date', $now->toDateString())
                          ->where('block_end_time', '<=', $now->toTimeString());
                    });
            })
            ->update([
                'is_blocked' => false,
                'block_reason' => null,
                'block_date' => null,
                'block_start_time' => null,
                'block_end_time' => null,
            ]);

        $this->info('Expired blocks cleared successfully.');
        return self::SUCCESS;
    }
}

