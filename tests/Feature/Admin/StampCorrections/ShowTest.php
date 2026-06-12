<?php

namespace Tests\Feature\Admin\StampCorrections;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakTimeCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stamp_correction_show_page_can_be_rendered(): void
    {
        $attendance = Attendance::factory()->create();
        $breakTime  = BreakTime::factory()
            ->recycle($attendance)
            ->create();
        $attendanceCorrection = AttendanceCorrection::factory()
            ->recycle($attendance)
            ->create();
        $breakTimeCorrection = BreakTimeCorrection::factory()
            ->withinAttendanceCorrection($attendanceCorrection)
            ->recycle($breakTime)
            ->create();

        $response = $this
            ->actingAs(User::factory()->admin()->create())
            ->get('stamp_correction_request/approve/'.$attendanceCorrection->id);

        $response->assertOk();
        $response->assertSee('勤怠詳細');
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));
        $response->assertSee($attendanceCorrection->clocked_in_at->format('H:i'));
        $response->assertSee($attendanceCorrection->clocked_out_at->format('H:i'));
        $response->assertSee($breakTimeCorrection->started_at->format('H:i'));
        $response->assertSee($breakTimeCorrection->ended_at->format('H:i'));
        $response->assertSee($attendanceCorrection->remarks);
        $response->assertSee(
            'action="'.route('admin.stamp-corrections.approve', $attendanceCorrection).'"',
            false,
        );
    }

    public function test_admin_can_approve_stamp_correction(): void
    {
        $admin      = User::factory()->admin()->create();
        $attendance = Attendance::factory()
            ->hasNonOverlappingBreakTimes(count: 1)
            ->create();
        $breakTime            = BreakTime::first();
        $attendanceCorrection = AttendanceCorrection::factory()
            ->recycle($attendance)
            ->create();
        $breakTimeCorrection = BreakTimeCorrection::factory()
            ->withinAttendanceCorrection($attendanceCorrection)
            ->recycle($breakTime)
            ->create();

        $this
            ->actingAs($admin)
            ->get('stamp_correction_request/approve/'.$attendanceCorrection->id)
            ->assertOk();

        $response = $this
            ->actingAs($admin)
            ->put('stamp_correction_request/approve/'.$attendanceCorrection->id);

        $response->assertRedirectBack();
        $this->assertDatabaseHas('attendances', [
            'clocked_in_at'  => $attendanceCorrection->clocked_in_at,
            'clocked_out_at' => $attendanceCorrection->clocked_out_at,
        ]);
        $this->assertDatabaseHas('attendance_corrections', [
            'id'     => $attendanceCorrection->id,
            'status' => ApprovalStatus::Approved,
        ]);
        $this->assertDatabaseHas('break_times', [
            'started_at' => $breakTimeCorrection->started_at,
            'ended_at'   => $breakTimeCorrection->ended_at,
        ]);
    }
}
