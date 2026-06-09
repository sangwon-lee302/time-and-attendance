<?php

namespace Database\Factories;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceCorrection>
 */
class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockedInAt = fake()->dateTimeBetween(today()->startOfDay());

        return [
            'attendance_id'  => Attendance::factory(),
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => fake()->dateTimeBetween($clockedInAt),
            'remarks'        => fake()->realText(),
        ];
    }

    /**
     * Indicate that the model's status has to be 'approved'.
     */
    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ApprovalStatus::Approved,
        ]);
    }
}
