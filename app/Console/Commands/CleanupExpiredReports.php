<?php

namespace App\Console\Commands;

use App\Models\Report;
use Illuminate\Console\Command;

class CleanupExpiredReports extends Command
{
    protected $signature = 'reports:cleanup';
    protected $description = 'Delete expired parking reports';

    public function handle()
    {
        $count = Report::where('expires_at', '<=', now())->subHours(3)->delete();
        $this->info("Deleted {$count} expired reports.");
        return 0;
    }
}
