<?php

namespace Tests\Feature\Admin\StampCorrections;

use App\Models\AttendanceCorrection;
use App\Models\BreakTimeCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock admin user.
     * 
     * @var User
     */
    protected $admin;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_pending_stamp_corrections_can_be_shown(): void
    {
        $attendanceCorrection = AttendanceCorrection::factory()->create();

        $this
            ->actingAs($this->admin)
            ->get('stamp_correction_request/list')
            ->assertOk()
            ->assertSee($attendanceCorrection->attendance->user->name)
            ->assertSee($attendanceCorrection->attendance->date->format('Y/m/d'))
            ->assertSee($attendanceCorrection->created_at->format('Y/m/d'));
    }

    public function test_approved_correction_can_be_shown(): void
    {
        $attendanceCorrection = AttendanceCorrection::factory()
            ->approved()
            ->create();

        $this
            ->actingAs($this->admin)
            ->get('stamp_correction_request/list')
            ->assertOk();

        $this
            ->actingAs($this->admin)
            ->get('stamp_correction_request/list?status=approved')
            ->assertOk()
            ->assertSee($attendanceCorrection->attendance->user->name)
            ->assertSee($attendanceCorrection->attendance->date->format('Y/m/d'))
            ->assertSee($attendanceCorrection->created_at->format('Y/m/d'));
    }

    public function test_correction_contents_can_be_shown(): void
    {
        $attendanceCorrection = AttendanceCorrection::factory()
            ->hasNonOverlappingBreakTimeCorrections(1)
            ->create();
        $breakTimeCorrection = BreakTimeCorrection::first();

        $this
            ->actingAs($this->admin)
            ->get('stamp_correction_request/approve/'.$attendanceCorrection->id)
            ->assertSee($attendanceCorrection->attendance->user->name)
            ->assertSee($attendanceCorrection->clocked_in_at->format('H:i'))
            ->assertSee($attendanceCorrection->clocked_out_at->format('H:i'))
            ->assertSee($breakTimeCorrection->started_at->format('H:i'))
            ->assertSee($breakTimeCorrection->ended_at->format('H:i'));
    }

    public function test_admin_can_approve_stamp_correction(): void
    {
        $attendanceCorrection = AttendanceCorrection::factory()
            ->hasNonOverlappingBreakTimeCorrections(1)
            ->create();
        $breakTimeCorrection = BreakTimeCorrection::first();

        $this
            ->actingAs($this->admin)
            ->get('stamp_correction_request/approve/'.$attendanceCorrection->id)
            ->assertOk();
        
        $this
            ->actingAs($this->admin)
            ->put(route('admin.stamp-corrections.approve', $attendanceCorrection));

        $this->assertDatabaseHas('attendances', [
            'clocked_in_at' => $attendanceCorrection->clocked_in_at,
            'clocked_out_at' => $attendanceCorrection->clocked_out_at,
        ]);
        $this->assertDatabaseHas('break_times', [
            'started_at' => $breakTimeCorrection->started_at,
            'ended_at' => $breakTimeCorrection->ended_at,
        ]);
    }
}
