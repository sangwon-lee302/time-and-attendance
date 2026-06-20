<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name'  => 'test admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'name'  => 'test user',
            'email' => 'user@example.com',
        ]);
        User::factory(5)->create();
    }
}
