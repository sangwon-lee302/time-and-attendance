<?php

namespace Tests\Feature\Admin\StampCorrections;

use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stamp_correction_index_page_can_be_rendered(): void
    {
        $attendanceCorrection = AttendanceCorrection::factory()->create();

        $response = $this
            ->actingAs(User::factory()->admin()->create())
            ->get('stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee('申請一覧');
        // check if links in navigation have appropriate query parameter
        $response->assertSee('status=pending');
        $response->assertSee('status=approved');
        $response->assertSee($attendanceCorrection->attendance->user->name);
        $response->assertSee(
            $attendanceCorrection->attendance->date->format('Y/m/d'),
        );
        $response->assertSee($attendanceCorrection->remarks);
        $response->assertSee($attendanceCorrection->created_at->format('Y/m/d'));
        $response->assertSee(
            'stamp_correction_request/approve/'.$attendanceCorrection->id,
        );
    }
}
