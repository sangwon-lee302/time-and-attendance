<?php

namespace Tests\Feature\TimeLog;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_break(): void
    {
        $user       = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $response = $this->actingAs($user)->get('attendance')->assertOk();

        $response->assertSee(
            'action="'.route('time-logs.break-start').'"',
            false,
        );

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.break-start'));

        $response->assertOk();
        $response->assertSeeText('休憩中');
    }

    public function test_user_can_take_multiple_breaks_per_day(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $this->actingAs($user)->post(route('time-logs.break-start'))->assertOk();

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('time-logs.break-end'));

        $response->assertOk();
        $response->assertSee(
            'action="'.route('time-logs.break-start').'"',
            false,
        );
    }

    public function test_user_can_end_break(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();
        
        $this->actingAs($user)->get('attendance')->assertOk();

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.break-start'));

        $response->assertOk();
        $response->assertSee(
            'action="'.route('time-logs.break-end').'"',
            false,
        );

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('time-logs.break-end'));

        $response->assertOk();
        $response->assertSee('出勤中');
    }

    public function test_user_can_end_multiple_breaks_per_day(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $this
            ->actingAs($user)
            ->post(route('time-logs.break-start'))
            ->assertOk();

        $this
            ->actingAs($user)
            ->post('time-logs.break-start')
            ->assertSee(
                'action='.route('time-logs.break-end').'"',
                false,
            );
    }

    public function test_break_can_be_shown_in_attendance_index_page(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this->actingAs($user)->post(route('time-logs.break-start'))->assertOk();

        $this->actingAs($user)->patch(route('time-logs.break-end'))->assertOk();

        $response = $this->actingAs($user)->get('attendance/list');

        $response->assertSee($attendance->total_break_time->format('%h:%I'));
    }
}
