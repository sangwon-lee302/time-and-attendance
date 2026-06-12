<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveAttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_status_can_be_resolved_when_user_has_not_clocked_in_yet(): void
    {
        $this->assertEquals(
            '勤務外',
            Attendance::resolveStatusForToday(User::factory()->create()),
        );
    }

    public function test_attendance_status_can_be_resolved_when_user_has_clocked_out(): void
    {
        $user = User::factory()->create();

        Attendance::factory()->recycle($user)->today()->create();

        $this->assertEquals('退勤済', Attendance::resolveStatusForToday($user));
    }

    public function test_attendance_status_can_be_resolved_when_user_is_taking_a_break(): void
    {
        $user = User::factory()->create();

        Attendance::factory()->recycle($user)
            ->today()
            ->notClockedOut()
            ->hasNonOverlappingBreakTimes(shouldEndLastBreakTime: false)
            ->create();

        $this->assertEquals('休憩中', Attendance::resolveStatusForToday($user));
    }

    public function test_attendance_status_can_be_resolved_when_user_is_working(): void
    {
        $user = User::factory()->create();

        Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this->assertEquals('出勤中', Attendance::resolveStatusForToday($user));
    }
}
