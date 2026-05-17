<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BreakTime>
 */
class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 years', 'now');
        $endedAt   = fake()->dateTimeBetween($startedAt, 'now');

        return [
            'attendance_id' => Attendance::factory(),
            'started_at'    => $startedAt,
            'ended_at'      => $endedAt,
            'created_at'    => $startedAt,
            'updated_at'    => $endedAt,
        ];
    }

    /**
     * Indicate that the model's started at and ended at should be in between
     * attendance's clocked in time and clocked out time.
     */
    public function withinAttendance(Attendance $attendance): static
    {
        return $this->state(function () use ($attendance) {
            $clockedInAt  = $attendance->clocked_in_at;
            $clockedOutAt = $attendance->clocked_out_at;

            $startedAt = fake()->dateTimeBetween($clockedInAt, $clockedOutAt);
            $endedAt   = fake()->dateTimeBetween($startedAt, $clockedOutAt);

            return [
                'attendance_id' => $attendance->id,
                'started_at'    => $startedAt,
                'ended_at'      => $endedAt,
                'created_at'    => $startedAt,
                'updated_at'    => $endedAt,
            ];
        });
    }

    /**
     * Indicate that the model's ended time should be null.
     */
    public function notEnded(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at'   => null,
            'updated_at' => $attributes['started_at'],
        ]);
    }
}
