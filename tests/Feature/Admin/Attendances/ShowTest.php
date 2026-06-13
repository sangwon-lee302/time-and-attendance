<?php

namespace Tests\Feature\Admin\Attendances;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Override;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock admin user.
     * 
     * @var User
     */
    protected $admin;

    /**
     * A mock attendance.
     * 
     * @var Attendance
     */
    protected $attendance;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->attendance = Attendance::factory()
            ->hasNonOverlappingBreakTimes()
            ->create();
    }

    public function test_attendance_details_are_shown(): void
    {
        $this
            ->accessToAttendanceShowPage()
            ->assertOk()
            ->assertSee($this->attendance->user->name)
            ->assertSee($this->attendance->date->format('Y年'))
            ->assertSee($this->attendance->date->format('n月j日'));
    }

    public function test_admin_cannot_make_correction_with_invalid_clock_in_and_out_time(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections([
                'clocked_in_at' => '9:01',
                'clocked_out_at' => '9:00',
            ])
            ->assertRedirect('admin/attendance/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'clocked_in_at' => '出勤時間もしくは退勤時間が不適切な値です'
            ]);
    }

    public function test_admin_cannot_make_stamp_correction_with_too_late_break_start_time(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections([
                'clocked_out_at'        => '10:00',
                'breaks[0][started_at]' => '10:01',
            ])
            ->assertRedirect('admin/attendance/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'breaks.0.started_at' => '休憩時間が不適切な値です',
            ]);
    }

    public function test_admin_cannot_make_stamp_correction_with_too_late_break_end_time(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections([
                'clocked_out_at'      => '10:00',
                'breaks[0][ended_at]' => '10:01',
            ])
            ->assertRedirect('admin/attendance/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'breaks.0.ended_at' => '休憩時間もしくは退勤時間が不適切な値です',
            ]);
    }

    public function test_admin_cannot_make_stamp_correction_with_empty_remarks(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections(['remarks' => ''])
            ->assertRedirect('admin/attendance/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'remarks' => '備考を記入してください',
            ]);
    }
    
    protected function accessToAttendanceShowPage(): TestResponse
    {
        return $this
            ->actingAs($this->admin)
            ->get('admin/attendance/'.$this->attendance->id);
    }

    protected function requestCorrections(array $data): TestResponse
    {
        return $this
            ->actingAs($this->admin)
            ->put(route('admin.attendances.update', array_merge(
                ['attendance' => $this->attendance],
                $data,
            )));
    }    
}
