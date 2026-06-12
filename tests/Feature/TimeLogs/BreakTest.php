<?php

namespace Tests\Feature\TimeLogs;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_break(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $response = $this->actingAs($user)->get('attendance')->assertOk();

        $response->assertSee(
            'action="'.route('time-logs.start-break').'"',
            false,
        );

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.start-break'));

        $response->assertOk();
        $response->assertSeeText('休憩中');
    }

    public function test_user_can_start_multiple_breaks_per_day(): void
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
            ->post(route('time-logs.start-break'))
            ->assertRedirect('attendance');

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('time-logs.end-break'));

        $response->assertOk();
        $response->assertSee(
            'action="'.route('time-logs.start-break').'"',
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
            ->post(route('time-logs.start-break'));

        $response->assertOk();
        $response->assertSee(
            'action="'.route('time-logs.end-break').'"',
            false,
        );

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('time-logs.end-break'));

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
            ->post(route('time-logs.start-break'))
            ->assertRedirect('attendance');

        $this
            ->actingAs($user)
            ->patch(route('time-logs.end-break'))
            ->assertRedirect('attendance');

        $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.start-break'))
            ->assertSee(
                'action="'.route('time-logs.end-break').'"',
                false,
            );
    }

    public function test_break_can_be_shown_in_attendance_index_page(): void
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()
            ->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this
            ->actingAs($user)
            ->post(route('time-logs.start-break'))
            ->assertRedirect('attendance');

        $this
            ->actingAs($user)
            ->patch(route('time-logs.end-break'))
            ->assertRedirect('attendance');

        $response = $this->actingAs($user)->get('attendance/list');

        $response->assertSee($attendance->total_break_time->format('%h:%I'));
    }
}
