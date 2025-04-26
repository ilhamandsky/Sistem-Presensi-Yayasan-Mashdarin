<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Barcode;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'barcode_id' => Barcode::factory(),
            'date' => now()->toDateString(),
            'time_in' => now(),
            'time_out' => now()->addHours(8),
            'shift_id' => Shift::factory(),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'status' => 'present',
            'note' => $this->faker->sentence,
            'attachment' => null,
        ];
    }
}
