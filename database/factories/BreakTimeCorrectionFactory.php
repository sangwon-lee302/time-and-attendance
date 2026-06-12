<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakTimeCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BreakTimeCorrection>
 */
class BreakTimeCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween(today()->startOfDay());

        return [
            'attendance_correction_id' => AttendanceCorrection::factory(),
            'break_time_id'            => BreakTime::factory(),
            'started_at'               => $startedAt,
            'ended_at'                 => fake()->dateTimeBetween($startedAt),
        ];
    }
}
