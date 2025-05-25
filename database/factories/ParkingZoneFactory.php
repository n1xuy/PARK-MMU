<?php

namespace Database\Factories;

use App\Models\ParkingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingZoneFactory extends Factory
{
    protected $model = ParkingZone::class;

    public function definition()
    {
        return [
            'name' => 'P' . $this->faker->unique()->numberBetween(1, 15),
            'status' => 'empty',
            'zone_number' => $this->faker->unique()->numberBetween(1, 15),
            'location' => 'MMU Cyberjaya Block ' . $this->faker->randomLetter,
            'total_reports' => 0
        ];
    }
}
