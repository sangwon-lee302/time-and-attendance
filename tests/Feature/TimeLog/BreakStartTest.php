<?php

namespace Tests\Feature\TimeLog;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakStartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_break_successfully(): void
    {
        // freeze time to ensure consistent test results
        $this->freezeTime();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.break-start'));

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'started_at'    => now(),
            'ended_at'      => null,
        ]);

        $response->assertOk();
        $this->assertEquals(url('attendance'), request()->url());
        $response->assertViewIs('time-logs.create');
        // check if attendance status is shown correctly
        $response->assertSeeText('休憩中');
        // check if break-end button is shown
        $response->assertSee(route('time-logs.break-end'));
    }
}
