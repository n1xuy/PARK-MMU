<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SafeSeed extends Command
{   
    /** 
    
     *
     * @var string
     */
    protected $signature = 'app:safe-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (\App\Models\ParkingZone::count() === 0) {
        $this->call('db:seed', ['--class' => 'ParkingZonesSeeder']);
        $this->info('Seeded parking zones!');
        } else {
        $this->info('Parking zones already exist - skipping seeding');
        }
    }
}
