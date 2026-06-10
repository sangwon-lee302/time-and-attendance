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
     * Indicate that the model's started_at and ended_at should be in between the given
     * attendance's clocked_in_at and clocked_out_at. Also guarantee all the created
     * models not to have overlapping break time when creating multiple models at once.
     */
    public function withinAttendance(Attendance $attendance): static
    {
        $cursor = $attendance->clocked_in_at;

        return $this->state(function () use ($attendance, &$cursor) {
            $clockedOutAt = $attendance->clocked_out_at;

            $startedAt = fake()->dateTimeBetween($cursor, $clockedOutAt);
            $endedAt   = fake()->dateTimeBetween($startedAt, $clockedOutAt);

            $cursor = $endedAt;

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
     * Indicate that the model's ended_at should be null.
     */
    public function notEnded(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at'   => null,
            'updated_at' => $attributes['started_at'],
        ]);
    }
}
