<?php

namespace Database\Factories;

use App\ApplicationStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceCorrectionApplication>
 */
class AttendanceCorrectionApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $newClockedInAt = fake()->dateTime();

        return [
            'attendance_id'      => Attendance::factory(),
            'new_clocked_in_at'  => $newClockedInAt,
            'new_clocked_out_at' => fake()->dateTime($newClockedInAt),
            'remarks'            => fake()->realText(),
        ];
    }

    /**
     * Indicate that the model's status has to be 'approved'.
     */
    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::Approved,
        ]);
    }
}
