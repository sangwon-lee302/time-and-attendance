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
        $clockedInAt  = fake()->dateTimeBetween('-1 year');
        $clockedOutAt = fake()->dateTimeBetween($clockedInAt);

        return [
            'user_id' => User::factory(),
            'date'    => CarbonImmutable::instance($clockedInAt)
                ->format('Y-m-d'),
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => $clockedOutAt,
            'created_at'     => $clockedInAt,
            'updated_at'     => $clockedOutAt,
        ];
    }

    /**
     * Indicate that the model's date should be of the given date.
     * If date is null, use today instead.
     */
    public function ofDate(?CarbonImmutable $date = null): static
    {
        $date = $date ?? today();

        $clockedInAt  = fake()->dateTimeBetween($date->startOfDay());
        $clockedOutAt = fake()->dateTimeBetween($clockedInAt);

        return $this->state(fn () => [
            'date'           => $date->format('Y-m-d'),
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => $clockedOutAt,
            'created_at'     => $clockedInAt,
            'updated_at'     => $clockedOutAt,
        ]);
    }

    /**
     * Indicate that the model's date should be of the given month and are
     * different with each other when creating multiple models.
     * Use current month if year and month is null.
     */
    public function uniqueInMonth(?string $month = null, ?string $year = null): static
    {
        $month = $month ?? now()->format('m');
        $year  = $year ?? now()->format('Y');

        $date = CarbonImmutable::parse($year.'-'.$month);

        $shuffledDays = collect(range(1, $date->daysInMonth()))->shuffle();

        return $this->sequence(fn (Sequence $sequence) => [
            'date' => $date->day($shuffledDays[$sequence->index]),
        ]);
    }

    /**
     * Indicate that the model has associated non-overlapping break time resources.
     */
    public function hasNonOverlappingBreakTimes(
        int $count = 2,
        bool $shouldEndLastBreakTime = true
    ): static {
        return $this->afterCreating(function (Attendance $attendance) use (
            $count,
            $shouldEndLastBreakTime,
        ): void {
            if ($count <= 0) {
                return;
            }

            $clockedInAt  = $attendance->clocked_in_at;
            $clockedOutAt = $attendance->clocked_out_at ?? now();
            $segmentSize  = $clockedInAt->diffInSeconds($clockedOutAt) / $count;
            for ($i = 0; $i < $count; $i++) {
                $segmentStart = $clockedInAt->addSeconds($segmentSize * $i);
                $segmentEnd   = $segmentStart->addSeconds($segmentSize);

                $startedAt = fake()->dateTimeBetween($segmentStart, $segmentEnd);
                $endedAt   = fake()->dateTimeBetween($startedAt, $segmentEnd);

                $shouldLeaveOpen = $i === $count - 1 && ! $shouldEndLastBreakTime;

                BreakTime::factory()->recycle($attendance)->create([
                    'started_at' => $startedAt,
                    'ended_at'   => $shouldLeaveOpen ? null : $endedAt,
                    'created_at' => $startedAt,
                    'updated_at' => $shouldLeaveOpen ? $startedAt : $endedAt,
                ]);
            }
        })
            ->when(
                ! $shouldEndLastBreakTime,
                fn (self $attendanceFactory) => $attendanceFactory->notClockedOut(),
            );
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
