<?php

namespace Tests\Feature\AttendanceCorrectionApplications;

use App\AttendanceCorrectionApplicationStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionApplication;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_make_attendance_correction_application_with_invalid_clock_in_and_out_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $response = $this->post(route('attendance-correction-applications.store', [
            'attendance'         => $attendance,
            'new_clocked_in_at'  => '9:01',
            'new_clocked_out_at' => '9:00',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'new_clocked_in_at' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_attendance_correction_application_with_too_early_break_start_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $response = $this->post(route('attendance-correction-applications.store', [
            'attendance'                => $attendance,
            'new_clocked_in_at'         => '9:00',
            'new_clocked_out_at'        => '10:00',
            'breaks[0][new_started_at]' => '8:59',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.new_started_at' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_attendance_correction_application_with_too_late_break_start_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $response = $this->post(route('attendance-correction-applications.store', [
            'attendance'                => $attendance,
            'new_clocked_in_at'         => '9:00',
            'new_clocked_out_at'        => '10:00',
            'breaks[0][new_started_at]' => '10:01',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.new_started_at' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_attendance_correction_application_with_too_late_break_end_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $response = $this->post(route('attendance-correction-applications.store', [
            'attendance'              => $attendance,
            'new_clocked_in_at'       => '9:00',
            'new_clocked_out_at'      => '10:00',
            'breaks[0][new_ended_at]' => '10:01',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.new_ended_at' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_attendance_correction_application_with_empty_remarks(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $response = $this->post(route('attendance-correction-applications.store', [
            'attendance' => $attendance,
            'remarks'    => '',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    public function test_user_can_make_attendance_correction_application_with_valid_input(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        $breakTime  = BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $newClockedInAt  = '9:00';
        $newClockedOutAt = '18:00';
        $newBreakTimes   = [
            ['new_started_at' => '12:00', 'new_ended_at' => '12:30'],
            ['new_started_at' => '15:00', 'new_ended_at' => '15:30'],
        ];

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('attendance-correction-applications.store', [
                'attendance'                => $attendance,
                'new_clocked_in_at'         => $newClockedInAt,
                'new_clocked_out_at'        => $newClockedOutAt,
                'breaks[0][break_time_id]'  => $breakTime->id,
                'breaks[0][new_started_at]' => $newBreakTimes[0]['new_started_at'],
                'breaks[0][new_ended_at]'   => $newBreakTimes[0]['new_ended_at'],
                'breaks[1][new_started_at]' => $newBreakTimes[1]['new_started_at'],
                'breaks[1][new_ended_at]'   => $newBreakTimes[1]['new_ended_at'],
                'remarks'                   => '空白ではない備考',
            ]));

        // check if attendance correction application is stored successfully
        $this->assertDatabaseHas('attendance_correction_applications', [
            'attendance_id'     => $attendance->id,
            'status'            => AttendanceCorrectionApplicationStatus::Pending,
            'new_clocked_in_at' => Carbon::createFromFormat(
                'Y-m-d G:i',
                $attendance->date->format('Y-m-d').' '.$newClockedInAt,
            ),
            'new_clocked_out_at' => Carbon::createFromFormat(
                'Y-m-d G:i',
                $attendance->date->format('Y-m-d').' '.$newClockedOutAt,
            ),
        ]);

        // check if break time correction applications are stored successfully
        $attendanceCorrectionApplication = AttendanceCorrectionApplication::first();
        $this->assertDatabaseHas('break_time_correction_applications', [
            'attendance_correction_application_id' => $attendanceCorrectionApplication->id,
            'break_time_id'                        => $breakTime->id,
            'new_started_at'                       => Carbon::createFromFormat(
                'Y-m-d G:i',
                $attendance->date->format('Y-m-d').' '.$newBreakTimes[0]['new_started_at'],
            ),
            'new_ended_at' => Carbon::createFromFormat(
                'Y-m-d G:i',
                $attendance->date->format('Y-m-d').' '.$newBreakTimes[0]['new_ended_at'],
            ),
        ]);
        $this->assertDatabaseHas('break_time_correction_applications', [
            'attendance_correction_application_id' => $attendanceCorrectionApplication->id,
            'break_time_id'                        => null,
            'new_started_at'                       => Carbon::createFromFormat(
                'Y-m-d G:i',
                $attendance->date->format('Y-m-d').' '.$newBreakTimes[1]['new_started_at'],
            ),
            'new_ended_at' => Carbon::createFromFormat(
                'Y-m-d G:i',
                $attendance->date->format('Y-m-d').' '.$newBreakTimes[1]['new_ended_at'],
            ),
        ]);

        $response->assertOk();
        $this->assertEquals(url('attendance/detail/'.$attendance->id), request()->url());
        $response->assertViewIs('attendances.show');
        $response->assertSee('*承認待ちのため修正はできません。');

        // check if attendance correction application form is disabled
        $response->assertDontSee('method="POST"');
    }
}
