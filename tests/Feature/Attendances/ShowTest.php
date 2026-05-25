<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use DOMDocument;
use DOMXPath;
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

        // check if attendance correction application form is enabled
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($response->getContent(), 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $this->assertEquals(1, $xpath->query('//form[@id="attendance-correction-application"]')->count());
        $this->assertEquals(10, $xpath->query('//input[@form="attendance-correction-application" and @type="text"]')->count());
        $this->assertEquals(1, $xpath->query('//textarea[@form="attendance-correction-application"]')->count());
    }
}
