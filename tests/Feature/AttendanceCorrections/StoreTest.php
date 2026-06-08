<?php

namespace Tests\Feature\AttendanceCorrections;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_make_stamp_correction_with_invalid_clock_in_and_out_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)
            ->get('attendance/detail/'.$attendance->id)
            ->assertOk();

        $response = $this->post(route('stamp-corrections.store', [
            'attendance'     => $attendance,
            'clocked_in_at'  => '9:01',
            'clocked_out_at' => '9:00',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'clocked_in_at' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_stamp_correction_with_too_early_break_start_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)
            ->get('attendance/detail/'.$attendance->id)
            ->assertOk();

        $response = $this->post(route('stamp-corrections.store', [
            'attendance'            => $attendance,
            'clocked_in_at'         => '9:00',
            'clocked_out_at'        => '10:00',
            'breaks[0][started_at]' => '8:59',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.started_at' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_stamp_correction_with_too_late_break_start_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)
            ->get('attendance/detail/'.$attendance->id)
            ->assertOk();

        $response = $this->post(route('stamp-corrections.store', [
            'attendance'            => $attendance,
            'clocked_in_at'         => '9:00',
            'clocked_out_at'        => '10:00',
            'breaks[0][started_at]' => '10:01',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.started_at' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_stamp_correction_with_too_late_break_end_time(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)->get('attendance/detail/'.$attendance->id)->assertOk();

        $response = $this->post(route('stamp-corrections.store', [
            'attendance'          => $attendance,
            'clocked_in_at'       => '9:00',
            'clocked_out_at'      => '10:00',
            'breaks[0][ended_at]' => '10:01',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'breaks.0.ended_at' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_user_cannot_make_stamp_correction_with_empty_remarks(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        BreakTime::factory()->withinAttendance($attendance)->create();

        $this->actingAs($user)
            ->get('attendance/detail/'.$attendance->id)
            ->assertOk();

        $response = $this->post(route('stamp-corrections.store', [
            'attendance' => $attendance,
            'remarks'    => '',
        ]));

        $response->assertRedirect('attendance/detail/'.$attendance->id);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    public function test_user_can_make_stamp_correction_with_valid_input(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->recycle($user)->create();
        $breakTime  = BreakTime::factory()
            ->withinAttendance($attendance)
            ->create();

        $this->actingAs($user)
            ->get('attendance/detail/'.$attendance->id)
            ->assertOk();

        $date = $attendance->date;

        $newClockedInAt  = $date->setTime(9, 0);
        $newClockedOutAt = $date->setTime(18, 0);
        $newBreakTimes   = [
            [
                'started_at' => $date->setTime(12, 0),
                'ended_at'   => $date->setTime(12, 30),
            ],
            [
                'started_at' => $date->setTime(15, 0),
                'ended_at'   => $date->setTime(15, 30),
            ],
        ];

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('stamp-corrections.store', [
                'attendance'               => $attendance,
                'clocked_in_at'            => $newClockedInAt->format('H:i'),
                'clocked_out_at'           => $newClockedOutAt->format('H:i'),
                'breaks[0][break_time_id]' => $breakTime->id,
                'breaks[0][started_at]'    => $newBreakTimes[0]['started_at']->format('H:i'),
                'breaks[0][ended_at]'      => $newBreakTimes[0]['ended_at']->format('H:i'),
                'breaks[1][started_at]'    => $newBreakTimes[1]['started_at']->format('H:i'),
                'breaks[1][ended_at]'      => $newBreakTimes[1]['ended_at']->format('H:i'),
                'remarks'                  => '備考',
            ]));

        // check if attendance correction is stored successfully
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id'  => $attendance->id,
            'status'         => ApprovalStatus::Pending,
            'clocked_in_at'  => $newClockedInAt,
            'clocked_out_at' => $newClockedOutAt,
        ]);

        // check if break time corrections are stored successfully
        $attendanceCorrection = AttendanceCorrection::first();
        $this->assertDatabaseHas('break_time_corrections', [
            'attendance_correction_id' => $attendanceCorrection->id,
            'break_time_id'            => $breakTime->id,
            'started_at'               => $newBreakTimes[0]['started_at'],
            'ended_at'                 => $newBreakTimes[0]['ended_at'],
        ]);
        $this->assertDatabaseHas('break_time_corrections', [
            'attendance_correction_id' => $attendanceCorrection->id,
            'break_time_id'            => null,
            'started_at'               => $newBreakTimes[1]['started_at'],
            'ended_at'                 => $newBreakTimes[1]['ended_at'],
        ]);

        $response->assertOk();
        $this->assertEquals(
            url('attendance/detail/'.$attendance->id),
            request()->url()
        );
        $response->assertViewIs('attendances.show');
        $response->assertSee('*承認待ちのため修正はできません。');
        // check if stamp correction form is disabled
        $response->assertDontSee('id="stamp-correction"', false);
    }
}
