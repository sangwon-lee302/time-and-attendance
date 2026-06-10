<?php

namespace Tests\Feature\Admin\Attendances;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attendance_daily_index_view_can_be_rendered(): void
    {
        $this->freezeTime();

        $admin       = User::factory()->admin()->create();
        $users       = User::factory(10)->create();
        $attendances = [];
        foreach ($users as $user) {
            $attendances[] = Attendance::factory()
                ->recycle($user)
                ->today()
                ->create();

            BreakTime::factory()
                ->withinAttendance($attendances[count($attendances) - 1])
                ->create();
        }

        $request = $this->actingAs($admin)->get('admin/attendance/list');

        $request->assertOk();
        $request->assertSee(now()->format('Y年n月j日').'の勤怠');
        // check if links for yesterday and tomorrow exists
        $request->assertSee('date='.now()->subDay()->format('Y-m-d'));
        $request->assertSee('date='.now()->addDay()->format('Y-m-d'));
        foreach ($users as $user) {
            $request->assertSee($user->name);
        }
        foreach ($attendances as $attendance) {
            $request->assertSee($attendance->clocked_in_at->format('H:i'));
            $request->assertSee($attendance->clocked_out_at->format('H:i'));
            $request->assertSee($attendance->total_break_time->format('%h:%I'));
            $request->assertSee($attendance->total_working_time->format('%h:%I'));
            // check if links for admin attendance show page exists
            $request->assertSee(
                'href="'.url('admin/attendance/'.$attendance->id).'"',
                false,
            );
        }
    }
}
