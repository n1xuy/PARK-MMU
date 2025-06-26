<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ParkManageController;
use Illuminate\Support\Facades\Log;

class ProcessParkingBlocks extends Command
{
    protected $signature = 'parking:process-blocks';
    protected $description = 'Process scheduled and recurring parking blocks';

    public function handle()
    {
        Log::info('ProcessParkingBlocks command executed at ' . now());
        $controller = new ParkManageController();
        
        $this->info('Processing parking blocks...');
        
        $scheduled = $controller->processScheduledBlocks();
        $recurring = $controller->processRecurringBlocks();
        $expired = $controller->autoUnblockExpiredZones();
        
        $this->info("✅ Processed:");
        $this->info("   - {$scheduled} scheduled blocks activated");
        $this->info("   - {$recurring} recurring blocks activated"); 
        $this->info("   - {$expired} expired blocks unblocked");
    }
}