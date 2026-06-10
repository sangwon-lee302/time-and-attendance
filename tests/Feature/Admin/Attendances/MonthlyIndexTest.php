<?php

namespace Tests\Feature\Admin\Attendances;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_monthly_index_page_can_be_rendered(): void
    {
        $this->freezeTime();

        $user        = User::factory()->create();
        $admin       = User::factory()->admin()->create();
        $attendances = Attendance::factory(20)
            ->recycle($user)
            ->uniqueDateInMonth(now()->format('Y-m'))
            ->create();
        foreach ($attendances as $attendance) {
            BreakTime::factory(2)
                ->withinAttendance($attendance)
                ->create();
        }

        $response = $this
            ->actingAs($admin)
            ->get('admin/attendance/staff/'.$user->id);

        $response->assertOk();
        // $response->
    }

    public function test_admin_user_can_export_attendances_as_csv(): void
    {
        $this->freezeTime();

        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();
        foreach (Attendance::factory(5)->recycle($user)->create() as $attendance) {
            BreakTime::factory(2)->withinAttendance($attendance)->create();
        }

        $this
            ->actingAs($admin)
            ->get('admin/attendance/staff/'.$user->id)
            ->assertOk();

        $response = $this->actingAs($admin)->get(route('admin.attendances.export', [
            'user'  => $user,
            'month' => now()->format('Y-m'),
        ]));

        $response->assertOk();

        $response->assertHeaderContains('Content-Type', 'text/csv');
        $response->assertHeader(
            'Content-Disposition',
            'attachment; filename="attendances.csv"',
        );

        $csvContent = $response->streamedContent();

        $this->assertStringContainsString("\xEF\xBB\xBF", $csvContent);
        $this->assertStringContainsString('日付,出勤,退勤,休憩,合計', $csvContent);
        $attendances = Attendance::whereBetween('date', [
            today()->startOfDay(),
            today()->endOfDay(),
        ])
            ->with('breakTimes', fn ($query) => $query->whereNotNull('ended_at')
                ->select('id', 'attendance_id', 'started_at', 'ended_at')
            )
            ->get();
        foreach ($attendances as $attendance) {
            $this->assertStringContainsString(
                $attendance->date->isoFormat('MM/DD(ddd)'),
                $csvContent,
            );
            $this->assertStringContainsString(
                $attendance->clocked_in_at->format('H:i'),
                $csvContent,
            );
            $this->assertStringContainsString(
                $attendance->clocked_out_at->format('H:i'),
                $csvContent,
            );
            $this->assertStringContainsString(
                $attendance->total_break_time->format('%h:%I'),
                $csvContent,
            );
            $this->assertStringContainsString(
                $attendance->total_working_time->format('%h:%I'),
                $csvContent,
            );
        }
    }
}
