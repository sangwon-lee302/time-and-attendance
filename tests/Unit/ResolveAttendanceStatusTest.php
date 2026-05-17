<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveAttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_correctly_resolves_the_attendance_status_when_the_user_has_not_attended_today(): void
    {
        $this->assertEquals('勤務外',
            Attendance::resolveStatusForToday(User::factory()->create())
        );
    }

    public function test_correctly_resolves_the_attendance_status_when_the_user_has_clocked_out(): void
    {
        $user = User::factory()->create();

        Attendance::factory()->recycle($user)->today()->create();

        $this->assertEquals('退勤済',
            Attendance::resolveStatusForToday($user)
        );
    }

    public function test_correctly_resolves_the_attendance_status_when_a_user_is_taking_a_break(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        BreakTime::factory()->withinAttendance($attendance)->notEnded()->create();

        $this->assertEquals('休憩中',
            Attendance::resolveStatusForToday($user)
        );
    }

    public function test_correctly_resolves_the_attendance_status_when_a_user_is_working(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->assertEquals('出勤中',
            Attendance::resolveStatusForToday($user)
        );
    }
}
