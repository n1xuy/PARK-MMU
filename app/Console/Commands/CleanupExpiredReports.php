<?php

namespace App\Console\Commands;

use App\Models\Report;
use Illuminate\Console\Command;

class CleanupExpiredReports extends Command
{
    protected $signature = 'reports:cleanup';
    protected $description = 'Delete expired parking reports';

    public function handle(): int
    {
        $this->info('Cleaning up expired reports...');
        $count = Report::where('expires_at', '<=', now())->subHours(3)->delete();
        $this->info("Deleted {$count} expired reports.");
        return self ::SUCCESS;
    }
}
