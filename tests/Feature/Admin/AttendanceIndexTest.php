<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attendance_index_view_is_rendered(): void
    {
        $this->freezeTime();

        $adminUser  = User::factory()->admin()->create();
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->today()->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $request = $this->actingAs($adminUser)->get('admin/attendance/list');

        $request->assertOk();
        $request->assertSee(now()->isoFormat('LL').'の勤怠');
        // dump($request->getContent());
        // check if links for yesterday and tomorrow exists
        $request->assertSee('date='.now()->subDay()->format('Y-m-d'));
        $request->assertSee('date='.now()->addDay()->format('Y-m-d'));
        $request->assertSee($user->name);
        $request->assertSee($attendance->clocked_in_at->format('H:i'));
        $request->assertSee($attendance->clocked_out_at->format('H:i'));
        $request->assertSee($attendance->total_break_time->format('%h:%I'));
        $request->assertSee($attendance->total_working_time->format('%h:%I'));
        // check if links for attendance show page exists
        $request->assertSee('href="'.url('admin/attendance/'.$attendance->id).'"', false);
    }
}
