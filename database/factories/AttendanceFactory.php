<?php

namespace Database\Factories;

use App\Models\Attendance;
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
    public function uniqueDateInMonth(string $yearAndMonth): static
    {
        $date = CarbonImmutable::parse($yearAndMonth);

        $shuffledDays = range(1, $date->daysInMonth());
        shuffle($shuffledDays);

        return $this->sequence(fn (Sequence $sequence) => [
            'date' => $date->day($shuffledDays[$sequence->index]),
        ]);
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
