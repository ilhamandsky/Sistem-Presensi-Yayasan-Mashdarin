<?php

namespace Database\Factories;

use App\Models\Barcode;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarcodeFactory extends Factory
{
    protected $model = Barcode::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'value' => $this->faker->uuid,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'radius' => 100,
        ];
    }
}
