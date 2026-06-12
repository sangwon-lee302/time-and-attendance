<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockedInAt  = fake()->dateTimeBetween('-1 year', 'now');
        $clockedOutAt = fake()->dateTimeBetween($clockedInAt, 'now');

        return [
            'user_id'        => User::factory(),
            'date'           => CarbonImmutable::instance($clockedInAt)->format('Y-m-d'),
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => $clockedOutAt,
            'created_at'     => $clockedInAt,
            'updated_at'     => $clockedOutAt,
        ];
    }

    /**
     * Indicate that the model's date should be today.
     */
    public function today(): static
    {
        $clockedInAt  = fake()->dateTimeBetween(today()->startOfDay(), 'now');
        $clockedOutAt = fake()->dateTimeBetween($clockedInAt, 'now');

        return $this->state(fn () => [
            'date'           => today()->format('Y-m-d'),
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => $clockedOutAt,
            'created_at'     => $clockedInAt,
            'updated_at'     => $clockedOutAt,
        ]);
    }

    /**
     * Indicate that the model's date should be of the given month and are
     * different with each other when creating multiple models.
     */
    public function uniqueInMonth(string $yearAndMonth): static
    {
        $date = CarbonImmutable::parse($yearAndMonth);

        $shuffledDays = range(1, $date->daysInMonth());
        shuffle($shuffledDays);

        return $this->sequence(fn (Sequence $sequence) => [
            'date' => $date->day($shuffledDays[$sequence->index]),
        ]);
    }

    /**
     * Indicate that the model has non-overlapping associated break time resources.
     */
    public function hasNonOverlappingBreakTimes(
        int $count = 2,
        bool $leaveLastBreakTimeOpen = false
    ): static {
        return $this->afterCreating(function (Attendance $attendance) use (
            $count,
            $leaveLastBreakTimeOpen,
        ): void {
            if ($count <= 0) {
                return;
            }

            $clockedInAt  = $attendance->clocked_in_at;
            $clockedOutAt = $attendance->clocked_out_at ?? now();

            $segmentSize = intdiv(
                $clockedOutAt->diffInSeconds($clockedInAt, absolute: true),
                $count,
            );

            for ($i = 0; $i < $count; $i++) {
                $segmentStart = $clockedInAt->addSeconds($segmentSize * $i);
                $segmentEnd   = $segmentStart->addSeconds($segmentSize);

                $startedAt = fake()->dateTimeBetween($segmentStart, $segmentEnd);
                $endedAt   = fake()->dateTimeBetween($startedAt, $segmentEnd);

                $shouldLeaveOpen = $i === $count - 1 && $leaveLastBreakTimeOpen;

                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'started_at'    => $startedAt,
                    'ended_at'      => $shouldLeaveOpen ? null : $endedAt,
                    'created_at'    => $startedAt,
                    'updated_at'    => $shouldLeaveOpen ? $startedAt : $endedAt,
                ]);
            }
        });
    }

    /**
     * Indicate that the model's clocked out time should be null.
     */
    public function notClockedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'clocked_out_at' => null,
            'updated_at'     => $attributes['clocked_in_at'],
        ]);
    }
}
