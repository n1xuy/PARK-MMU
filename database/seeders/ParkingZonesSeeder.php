<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParkingZonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
        ['zone_number' => 1, 'name' => 'P1', 'status' => 'empty', 'location' => 'MMU Block A'],
        ['zone_number' => 2, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 3, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 4, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 5, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 6, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 7, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 8, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 9, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 10, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 11, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 12, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 13, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 14, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
        ['zone_number' => 15, 'name' => 'P2', 'status' => 'empty', 'location' => 'MMU Block B'],
    ];

    foreach ($zones as $zone) {

        \App\Models\ParkingZone::updateOrCreate(
            ['zone_number' => $zone['zone_number']], // Search condition
            $zone // Data to insert/update
        );
    }
    }
}
