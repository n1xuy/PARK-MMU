<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CleanupExpiredReports;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CleanupExpiredReports::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reports:cleanup')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
