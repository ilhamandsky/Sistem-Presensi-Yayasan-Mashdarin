<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Lutfi',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'group' => 'admin',
        ]);

        // User biasa
        User::factory()->create([
            'name' => 'Indah',
            'email' => Null,
            'password' => Null,
            'group' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Silvi',
            'email' => Null,
            'password' => Null,
            'group' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Rahma',
            'email' => Null,
            'password' => Null,
            'group' => 'user',
        ]);
        
        User::factory()->create([
            'name' => 'Cahyo',
            'email' => Null,
            'password' => Null,
            'group' => 'user',
        ]);
        User::factory()->create([
            'name' => 'Lita',
            'email' => Null,
            'password' => Null,
            'group' => 'user',
        ]);

    }
}
