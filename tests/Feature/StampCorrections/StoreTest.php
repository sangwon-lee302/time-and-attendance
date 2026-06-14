<?php

namespace Tests\Feature\StampCorrections;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Override;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock user.
     *
     * @var User
     */
    protected $user;

    /**
     * A mock attendance associated with the mock user.
     *
     * @var Attendance
     */
    protected $attendance;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user       = User::factory()->create();
        $this->attendance = Attendance::factory()
            ->recycle($this->user)
            ->hasNonOverlappingBreakTimes(1)
            ->create();
    }

    public function test_user_cannot_make_stamp_correction_with_invalid_clock_in_and_out_time(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections([
                'clocked_in_at'  => '9:01',
                'clocked_out_at' => '9:00',
            ])
            ->assertRedirect('attendance/detail/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'clocked_in_at' => '出勤時間もしくは退勤時間が不適切な値です',
            ]);
    }

    public function test_user_cannot_make_stamp_correction_with_too_late_break_start_time(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections([
                'clocked_out_at'        => '10:00',
                'breaks[0][started_at]' => '10:01',
            ])
            ->assertRedirect('attendance/detail/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'breaks.0.started_at' => '休憩時間が不適切な値です',
            ]);
    }

    public function test_user_cannot_make_stamp_correction_with_too_late_break_end_time(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections([
                'clocked_out_at'      => '10:00',
                'breaks[0][ended_at]' => '10:01',
            ])
            ->assertRedirect('attendance/detail/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'breaks.0.ended_at' => '休憩時間もしくは退勤時間が不適切な値です',
            ]);
    }

    public function test_user_cannot_make_stamp_correction_with_empty_remarks(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this
            ->requestCorrections(['remarks' => ''])
            ->assertRedirect('attendance/detail/'.$this->attendance->id)
            ->assertSessionHasErrors([
                'remarks' => '備考を記入してください',
            ]);
    }

    public function test_user_can_make_stamp_correction_with_valid_input(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this->requestFullCorrections();

        $admin = User::factory()->admin()->create();
        $this
            ->actingAs($admin)
            ->get(
                'stamp_correction_request/approve/'.AttendanceCorrection::first()->id,
            )
            ->assertSee($this->user->name);

        $this
            ->actingAs($admin)
            ->get('stamp_correction_request/list')
            ->assertSee($this->user->name);
    }

    public function test_corrections_can_be_shown_at_correction_index_page(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this->requestFullCorrections();

        $this
            ->actingAs($this->user)
            ->get('stamp_correction_request/list')
            ->assertSee($this->user->name);
    }

    public function test_approved_corrections_can_be_shown_at_correction_index_page(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this->requestFullCorrections();

        AttendanceCorrection::query()->update([
            'status' => ApprovalStatus::Approved,
        ]);

        $this
            ->actingAs($this->user)
            ->get('stamp_correction_request/list?status=approved')
            ->assertSee($this->user->name);
    }

    public function test_user_can_jump_to_attendance_show_page_from_correction_index_page(): void
    {
        $this->accessToAttendanceShowPage()->assertOk();

        $this->requestFullCorrections();

        $this
            ->actingAs($this->user)
            ->get('stamp_correction_request/list')
            ->assertSee(
                'href="'.url('attendance/detail/'.$this->attendance->id).'"',
                false,
            );

        $this->accessToAttendanceShowPage()->assertOk();
    }

    protected function accessToAttendanceShowPage(): TestResponse
    {
        return $this
            ->actingAs($this->user)
            ->get('attendance/detail/'.$this->attendance->id);
    }

    protected function requestCorrections(array $data): TestResponse
    {
        return $this
            ->actingAs($this->user)
            ->post(route('stamp-corrections.store', array_merge(
                ['attendance' => $this->attendance],
                $data,
            )));
    }

    protected function requestFullCorrections(): TestResponse
    {
        return $this
            ->actingAs($this->user)
            ->requestCorrections([
                'clocked_in_at'  => '9:00',
                'clocked_out_at' => '18:00',
                'breaks'         => [
                    [
                        'break_time_id' => BreakTime::first()->id,
                        'started_at'    => '12:00',
                        'ended_at'      => '12:30',
                    ],
                    [
                        'started_at' => '15:00',
                        'ended_at'   => '15:30',
                    ],
                ],
                'remarks' => '備考',
            ]);
    }
}
