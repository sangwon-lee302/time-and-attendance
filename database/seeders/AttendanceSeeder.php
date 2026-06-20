<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('is_admin', false)->get();

        Attendance::factory(20)
            ->recycle($users)
            ->uniqueInMonth(now()->subMonth()->month)
            ->create();
        Attendance::factory(20)
            ->recycle($users)
            ->uniqueInMonth(now()->month)
            ->create();
        Attendance::factory(20)
            ->recycle($users)
            ->uniqueInMonth(now()->addMonth()->month)
            ->create();
    }
}
