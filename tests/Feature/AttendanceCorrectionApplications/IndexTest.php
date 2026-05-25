<?php

namespace Tests\Feature\AttendanceCorrectionApplications;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_correction_application_page_is_rendered_successfully(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();

        $response = $this->actingAs($user)->get('stamp_correction_request/list');

        $response->assertOk();
        // check if links in nav includes appropriate query parameters
        $response->assertSee('status=pending');
        $response->assertSee('status=approved');
    }

    public function test_pending_application_is_shown_successfully(): void
    {
        $user        = User::factory()->create();
        $attendance  = Attendance::factory()->recycle($user)->create();
        $remark      = '備考です';
        $application = AttendanceCorrectionApplication::factory()
            ->recycle($attendance)
            ->pending()
            ->create(['remarks' => $remark]);

        $response = $this->actingAs($user)->get('stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y/m/d'));
        $response->assertSee($remark);
        $response->assertSee($application->created_at->format('Y/m/d'));
        // check if links to attendances.show page exists
        $response->assertSee(url('attendance/detail/'.$attendance->id));
    }

    public function test_approved_application_is_shown_successfully(): void
    {
        $user        = User::factory()->create();
        $attendance  = Attendance::factory()->recycle($user)->create();
        $remark      = '備考です';
        $application = AttendanceCorrectionApplication::factory()
            ->recycle($attendance)
            ->approved()
            ->create(['remarks' => $remark]);

        $response = $this->actingAs($user)->get('stamp_correction_request/list?status=approved');

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y/m/d'));
        $response->assertSee($remark);
        $response->assertSee($application->created_at->format('Y/m/d'));
        // check if links to attendances.show page exists
        $response->assertSee(url('attendance/detail/'.$attendance->id));
    }
}
