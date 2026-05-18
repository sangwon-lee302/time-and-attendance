<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_index_page_is_shown_correctly(): void
    {
        $this->freezeTime();

        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->today()->create();
        BreakTime::factory(2)->withinAttendance($attendance)->create();

        $response = $this->actingAs($user)->get('attendance/list');

        $response->assertOk();
        // check if links for the previous and next month are present
        $response->assertSee(
            'href="'.url('attendance/list?month='.now()->subMonth()->format('Y-m')).'"',
            false
        );
        $response->assertSee(
            'href="'.url('attendance/list?month='.now()->addMonth()->format('Y-m')).'"',
            false
        );
        // check if dates are shown in the correct format
        $response->assertSeeText(now()->startOfMonth()->isoFormat('MM/DD(ddd)'));
        $response->assertSeeText(now()->endOfMonth()->isoFormat('MM/DD(ddd)'));
        $response->assertSeeText($attendance->total_break_time->format('%h:%I'));
        $response->assertSeeText($attendance->total_working_time->format('%h:%I'));
    }
}
