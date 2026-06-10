<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'test user',
            'email' => 'user@example.com',
        ]);
        User::factory()->admin()->create([
            'name'  => 'test admin',
            'email' => 'admin@example.com',
        ]);
    }
}
