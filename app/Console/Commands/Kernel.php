<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\ParkManageController;
use App\Console\Commands\CleanupExpiredReports;
use App\Console\Commands\ProcessParkingBlocks;
use App\Console\Commands\UnblockExpiredParkingZones;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\UnblockExpiredParking; // Add this import
use Illuminate\Support\Facades\Process;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CleanupExpiredReports::class,
        UnblockExpiredParkingZones::class, 
        ProcessParkingBlocks::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('list')->daily();
        $schedule->command('reports:cleanup')->hourly();
        $schedule->command('parking:unblock-expired')->everyMinute();
        $schedule->command('parking:process-blocks')->everyMinute();
       
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');    
    }
}