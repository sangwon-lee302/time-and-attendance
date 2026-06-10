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
     * Indicate the the model's started_at and ended_at should be in between the given
     * attendance correction's clocked_in_at and clocked_out_at. Also guarantee all the
     * created models not to have overlapping break time when creating multiple models
     * at once.
     */
    public function withinAttendanceCorrection(
        AttendanceCorrection $attendanceCorrection,
    ): static {
        $cursor = $attendanceCorrection->clocked_in_at;

        return $this->state(function () use ($attendanceCorrection, &$cursor) {
            $clockedOutAt = $attendanceCorrection->clocked_out_at;

            $startedAt = fake()->dateTimeBetween($cursor, $clockedOutAt);
            $endedAt   = fake()->dateTimeBetween($startedAt, $clockedOutAt);

            $cursor = $endedAt;

            return [
                'attendance_correction_id' => $attendanceCorrection->id,
                'started_at'               => $startedAt,
                'ended_at'                 => $endedAt,
            ];
        });
    }
}
