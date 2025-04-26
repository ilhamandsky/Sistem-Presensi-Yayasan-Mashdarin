<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition()
    {
        return [
            'name' => 'Shift ' . $this->faker->randomElement(['Pagi', 'Siang', 'Malam']),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ];
    }
}
