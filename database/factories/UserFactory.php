<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Division;
use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'nip' => $this->faker->unique()->numerify('NIP###'),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
            'raw_password' => 'password',
            'group' => 'user',
            'phone' => $this->faker->phoneNumber,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'education_id' => Education::factory(),
            'division_id' => Division::factory(),
            'job_title_id' => null,
        ];
    }
}
