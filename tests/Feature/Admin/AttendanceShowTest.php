<?php

namespace Tests\Feature\Admin;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attendance_show_page_is_rendered_correctly(): void
    {
        $this->freezeTime();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get('admin/attendance/'.Attendance::factory()
                ->recycle(User::factory()->create())
                ->today()
                ->create()
                ->id
            );

        $response->assertOk();
        $response->assertViewIs('attendances.show');
    }

    public function test_correction_is_made_successfully_by_admin_user(): void
    {
        $this->freezeTime();

        $adminUser = User::factory()->admin()->create();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->recycle($user)->today()->create();

        $attendanceCorrection = AttendanceCorrection::factory()
            ->recycle($user)
            ->create([
                'clocked_in_at'  => new DateTime('09:00'),
                'clocked_out_at' => new DateTime('18:00'),
            ]);

        $this->actingAs($adminUser)->get('admin/attendance/'.$attendance->id)
            ->assertOk();

        $response = $this->actingAs($adminUser)
            ->patch(route('admin.attendances.update', [
                'attendance'        => $attendance,
                'clocked_in_at' => $attendanceCorrection->clocked_in_at
                    ->format('H:i'),
                'clocked_out_at' => $attendanceCorrection->clocked_out_at
                    ->format('H:i'),
                'remarks' => $attendanceCorrection->remarks,
            ]));

        $response->assertRedirect('admin/attendance/'.$attendance->id);

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id'      => $attendance->id,
            'status'             => ApprovalStatus::Approved,
            'clocked_in_at'  => $attendanceCorrection->clocked_in_at,
            'clocked_out_at' => $attendanceCorrection->clocked_out_at,
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id'        => $user->id,
            'clocked_in_at'  => $attendanceCorrection->clocked_in_at,
            'clocked_out_at' => $attendanceCorrection->clocked_out_at,
        ]);
    }
}
