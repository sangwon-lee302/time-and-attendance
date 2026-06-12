<?php

namespace Tests\Feature\TimeLog;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in(): void
    {
        $this->freezeTime();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('attendance');

        // check if clock in button exists
        $response->assertSee('action="'.route('time-logs.clock-in').'"', false);

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.clock-in'));

        $this->assertDatabaseHas('attendances', [
            'user_id'        => $user->id,
            'date'           => today(),
            'clocked_in_at'  => now(),
            'clocked_out_at' => null,
        ]);

        $response->assertOk();
        $response->assertSeeText('出勤中');
    }

    public function test_clock_in_can_be_done_only_once_per_day(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->create();

        // check if clock in button doesn't exist
        $this
            ->actingAs($user)
            ->get('attendance')
            ->assertDontSee('action="'.route('time-logs.clock-in').'"', false);
    }

    public function test_attendance_can_be_shown_in_attendance_index_page(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $this->actingAs($user)->post(route('time-logs.clock-in'));

        $response = $this->actingAs($user)->get('attendance/list');

        $response->assertOk();
        $response->assertSee($attendance->clocked_in_at->format('H:i'));
    }
}
