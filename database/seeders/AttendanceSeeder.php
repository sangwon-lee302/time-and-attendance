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
        $user       = User::where('email', 'user@example.com')->first();
        $otherUsers = User::where('is_admin', false)
            ->whereNot('id', $user->id)
            ->get();

        foreach ([$user, $otherUsers] as $usersToRecycle) {
            foreach ([-1, 0, 1] as $monthOffset) {
                Attendance::factory(20)
                    ->recycle($usersToRecycle)
                    ->uniqueInMonth(now()->addMonths($monthOffset)->month)
                    ->create();
            }
        }
    }
}
