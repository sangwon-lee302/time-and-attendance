<?php

namespace Tests\Feature\TimeLog;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_end_break(): void
    {
        // freeze time to ensure consistent test results
        $this->freezeTime();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        BreakTime::factory()->withinAttendance($attendance)
            ->notEnded()
            ->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->put(route('time-logs.break-end'));

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'ended_at'      => now(),
        ]);

        $response->assertOk();
        $this->assertEquals(url('attendance'), request()->url());
        $response->assertViewIs('time-logs.create');
        // check if attendance status is shown correctly
        $response->assertSeeText('出勤中');
        // check if clock-out button is shown
        $response->assertSee(route('time-logs.clock-out'));
        // check if break-start button is shown
        $response->assertSee(route('time-logs.break-start'));
    }
}
