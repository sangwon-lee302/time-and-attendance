<?php

namespace Tests\Feature\TimeLog;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_out_successfully(): void
    {
        // freeze time to ensure consistent test results
        $this->freezeTime();

        $user = User::factory()->create();

        Attendance::factory()->recycle($user)
            ->today()
            ->notClockedOut()
            ->create();

        $this->actingAs($user)->get('attendance')->assertOk();

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->put(route('time-logs.clock-out'));

        $this->assertDatabaseHas('attendances', [
            'user_id'        => $user->id,
            'date'           => today(),
            'clocked_out_at' => now(),
        ]);

        $response->assertOk();
        $this->assertEquals(url('attendance'), request()->url());
        $response->assertViewIs('time-logs.create');
        // check if attendance status is shown correctly
        $response->assertSeeText('退勤済');
        // check if clock-in button is not shown
        $response->assertDontSee(url(route('time-logs.clock-in')));
        $response->assertSeeText('お疲れ様でした。');
    }
}
