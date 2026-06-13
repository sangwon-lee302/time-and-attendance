<?php

namespace Tests\Feature\Admin\Attendances;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attendance_show_page_can_be_rendered(): void
    {
        $this->freezeTime();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get('admin/attendance/'.Attendance::factory()
                ->recycle(User::factory()->create())
                ->ofDate()
                ->create()
                ->id
            );

        $response->assertOk();
        $response->assertViewIs('attendances.show');
    }

    public function test_stamp_correction_can_be_made_by_admin_user(): void
    {
        $this->freezeTime();

        $admin = User::factory()->admin()->create();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->recycle($user)->ofDate()->create();

        $newClockedInAt       = $attendance->date->setTime(9, 0);
        $newClockedOutAt      = $attendance->date->setTime(18, 0);
        $attendanceCorrection = AttendanceCorrection::factory()
            ->recycle([$user, $attendance])
            ->create([
                'clocked_in_at'  => $newClockedInAt,
                'clocked_out_at' => $newClockedOutAt,
            ]);

        $this
            ->actingAs($admin)
            ->get('admin/attendance/'.$attendance->id)
            ->assertOk();

        $response = $this
            ->actingAs($admin)
            ->put(route('admin.attendances.update', [
                'attendance'     => $attendance,
                'clocked_in_at'  => $newClockedInAt->format('H:i'),
                'clocked_out_at' => $newClockedOutAt->format('H:i'),
                'remarks'        => $attendanceCorrection->remarks,
            ]));

        $response->assertSessionHasNoErrors();

        $response->assertRedirect('admin/attendance/'.$attendance->id);

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id'  => $attendance->id,
            'status'         => ApprovalStatus::Approved,
            'clocked_in_at'  => $newClockedInAt->format('Y-m-d H:i:s'),
            'clocked_out_at' => $newClockedOutAt->format('Y-m-d H:i:s'),
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id'        => $user->id,
            'clocked_in_at'  => $newClockedInAt->format('Y-m-d H:i:s'),
            'clocked_out_at' => $newClockedOutAt->format('Y-m-d H:i:s'),
        ]);
    }
}
