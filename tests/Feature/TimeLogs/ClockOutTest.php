<?php

namespace Tests\Feature\TimeLogs;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_out(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->ofDate()
            ->notClockedOut()
            ->create();

        $response = $this->actingAs($user)->get('attendance');

        $response->assertOk();
        $response->assertSee(
            'action="'.route('time-logs.clock-out').'"',
            false,
        );

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('time-logs.clock-out'));

        $response->assertOk();
        $response->assertSeeText('退勤済');
    }

    public function test_clock_out_time_can_be_shown_in_attendance_index_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $this
            ->actingAs($user)
            ->post(route('time-logs.clock-in'))
            ->assertRedirect('attendance');

        $this
            ->actingAs($user)
            ->patch(route('time-logs.clock-out'))
            ->assertRedirect('attendance');

        $attendance = Attendance::first();
        $this
            ->actingAs($user)
            ->get('attendance/list')
            ->assertSee($attendance->clocked_out_at->format('H:i'));
    }
}
