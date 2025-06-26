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
            ['zone_number' => 1, 'name' => 'FCI', 'status' => 'empty', 'location' => 'FCI', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 2, 'name' => 'FOM', 'status' => 'empty', 'location' => 'FCI & FOM', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 3, 'name' => 'FOM-S', 'status' => 'empty', 'location' => 'FOM', 'zone_type' => 'staff', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 4, 'name' => 'Visit', 'status' => 'empty', 'location' => 'Visitor Parking', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 5, 'name' => 'SB', 'status' => 'empty', 'location' => 'StarBee', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 6, 'name' => 'MMUS', 'status' => 'empty', 'location' => 'MMU Stadium', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 7, 'name' => 'MMUS2', 'status' => 'empty', 'location' => 'MMU Stadium Parking 2', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 8, 'name' => 'HB1', 'status' => 'empty', 'location' => 'Hostel Block 1', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 9, 'name' => 'FOE', 'status' => 'empty', 'location' => 'FOE', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 10, 'name' => 'Libry', 'status' => 'empty', 'location' => 'Library', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 11, 'name' => 'FMD', 'status' => 'empty', 'location' => 'FMD', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 12, 'name' => 'FCM', 'status' => 'empty', 'location' => 'FCM', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 13, 'name' => 'HB1A', 'status' => 'empty', 'location' => 'Hostel Block 1A', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 14, 'name' => 'Surau', 'status' => 'empty', 'location' => 'Surau', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 15, 'name' => 'HB4C', 'status' => 'empty', 'location' => 'Hostel Block 4C', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 16, 'name' => 'Field', 'status' => 'empty', 'location' => 'Field', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 17, 'name' => 'Secu', 'status' => 'empty', 'location' => 'Security Department', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 18, 'name' => 'Canten', 'status' => 'empty', 'location' => 'Canteen', 'zone_type' => 'student', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 19, 'name' => 'MSC-S', 'status' => 'empty', 'location' => 'MSC', 'zone_type' => 'staff', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 20, 'name' => 'STAD-S', 'status' => 'empty', 'location' => 'STAD', 'zone_type' => 'staff', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 21, 'name' => 'FCM-S', 'status' => 'empty', 'location' => 'FCM', 'zone_type' => 'staff', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 22, 'name' => 'FOE-S', 'status' => 'empty', 'location' => 'FOE', 'zone_type' => 'staff', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
            ['zone_number' => 23, 'name' => 'FMD-S', 'status' => 'empty', 'location' => 'FMD', 'zone_type' => 'staff', 'total_empty' => 0,'total_half_full' => 0,'total_full' => 0],
    ];

    foreach ($zones as $zone) {

        \App\Models\ParkingZone::updateOrCreate(
            ['zone_number' => $zone['zone_number']], // Search condition
            $zone // Data to insert/update
        );
    }
    }
}
