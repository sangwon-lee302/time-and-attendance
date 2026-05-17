<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'date'           => Carbon::instance($clockedInAt)->toDateString(),
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

        return $this->state(fn (array $attributes) => [
            'date'           => today()->toDateString(),
            'clocked_in_at'  => $clockedInAt,
            'clocked_out_at' => $clockedOutAt,
            'created_at'     => $clockedInAt,
            'updated_at'     => $clockedOutAt,
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
