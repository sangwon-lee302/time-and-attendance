<?php

namespace Tests\Feature\TimeLog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in(): void
    {
        // freeze time to ensure consistent test results
        $this->freezeTime();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('attendance');

        $response->assertOk();
        $response->assertSee('勤務外');
        // check if clock-in button exists
        $response->assertSee('action="'.route('time-logs.clock-in').'"', false);

        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('time-logs.clock-in'));

        $this->assertDatabaseHas('attendances', [
            'user_id'        => $user->id,
            'date'           => today(),
            'clocked_in_at'  => now(),
            'clocked_out_at' => null,
        ]);

        $response->assertOk();
        $this->assertEquals(url('attendance'), request()->url());
        $response->assertViewIs('time-logs.create');
        // check if attendance status is shown
        $response->assertSeeText('出勤中');
        // check if clock-in button is not shown
        $response->assertDontSee('action="'.route('time-logs.clock-in').'"', false);
        // check if clock-out button is shown
        $response->assertSee('action="'.route('time-logs.clock-out').'"', false);
        // check if break-start button is shown
        $response->assertSee('action="'.route('time-logs.break-start').'"', false);
    }
}
