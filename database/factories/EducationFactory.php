<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word . ' Degree',
        ];
    }
}
