<?php

namespace Tests\Feature\Attendances;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_show_page_can_be_rendered(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory(3)->withinAttendance($attendance)->create();

        $response = $this->actingAs($user)->get('attendance/detail/'.$attendance->id);

        $response->assertOk();
        $response->assertViewIs('attendances.show');
        $response->assertSeeInOrder(['名前', '日付', '出勤・退勤', '休憩2', '休憩3', '休憩4', '備考']);
        $response->assertDontSee('休憩1');
        // check if stamp correction form is enabled
        $response->assertSee('id="stamp-correction"', false);
    }
}
