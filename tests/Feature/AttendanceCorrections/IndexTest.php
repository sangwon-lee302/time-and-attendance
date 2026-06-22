<?php

namespace Tests\Feature\AttendanceCorrections;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_correction_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        Attendance::factory()->recycle($user)->create();

        $response = $this->actingAs($user)->get('stamp_correction_request/list');

        $response->assertOk();
        // check if links in navigation include appropriate query parameters
        $response->assertSee('status=pending');
        $response->assertSee('status=approved');
    }

    public function test_pending_attendance_corrections_can_be_shown(): void
    {
        $user                 = User::factory()->create();
        $attendance           = Attendance::factory()->recycle($user)->create();
        $remark               = '備考です';
        $attendanceCorrection = AttendanceCorrection::factory()
            ->ofAttendance($attendance)
            ->create(['remarks' => $remark]);

        $response = $this->actingAs($user)->get('stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y/m/d'));
        $response->assertSee($remark);
        $response->assertSee($attendanceCorrection->created_at->format('Y/m/d'));
        $response->assertSee(url('attendance/detail/'.$attendance->id));
    }

    public function test_approved_attendance_corrections_can_be_shown(): void
    {
        $user                 = User::factory()->create();
        $attendance           = Attendance::factory()->recycle($user)->create();
        $remark               = '備考です';
        $attendanceCorrection = AttendanceCorrection::factory()
            ->ofAttendance($attendance)
            ->approved()
            ->create(['remarks' => $remark]);

        $response = $this
            ->actingAs($user)
            ->get('stamp_correction_request/list?status=approved');

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y/m/d'));
        $response->assertSee($remark);
        $response->assertSee($attendanceCorrection->created_at->format('Y/m/d'));
        $response->assertSee(url('attendance/detail/'.$attendance->id));
    }
}
