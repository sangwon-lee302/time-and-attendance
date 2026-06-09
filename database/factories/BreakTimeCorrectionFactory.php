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

    /**
     * Indicate the the model's start time and end time should be in between the given
     * attendance correction's clocked in time and clocked out time.
     */
    public function withinAttendanceCorrection(
        AttendanceCorrection $attendanceCorrection,
    ): static {
        return $this->state(function () use ($attendanceCorrection) {
            $clockedInAt  = $attendanceCorrection->clocked_in_at;
            $clockedOutAt = $attendanceCorrection->clocked_out_at;

            $startedAt = fake()->dateTimeBetween($clockedInAt, $clockedOutAt);
            $endedAt   = fake()->dateTimeBetween($startedAt, $clockedOutAt);

            return [
                'attendance_correction_id' => $attendanceCorrection->id,
                'started_at'               => $startedAt,
                'ended_at'                 => $endedAt,
            ];
        });
    }
}
