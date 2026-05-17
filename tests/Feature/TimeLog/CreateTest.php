<?php

namespace Tests\Feature\TimeLog;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_time_log_creation_view_is_shown_successfully(): void
    {
        $randomDate = Carbon::create(
            rand(1000, 9999),
            rand(1, 12),
            rand(1, 28),
            rand(0, 23),
            rand(0, 59),
            rand(0, 59),
            'Asia/Tokyo'
        );
        Carbon::setTestNow($randomDate);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertOk();
        $response->assertSeeText('勤務外');
        $response->assertSeeText($randomDate->isoFormat('ll(ddd)'));
        $response->assertSeeText($randomDate->format('H:i'));
        // check if clock-in button is shown
        $response->assertSee(url(route('time-logs.clock-in')));

        Carbon::setTestNow();
    }

    public function test_user_can_clock_in_successfully(): void
    {
        $this->actingAs($this->user)->get('/attendance')->assertOk();

        $response = $this->followingRedirects()
            ->actingAs($this->user)
            ->post(route('time-logs.clock-in'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => today(),
            'clocked_in_at' => now(),
            'clocked_out_at' => null,
        ]);

        $response->assertOk();
        $this->assertEquals(url('/attendance'), request()->url());
        $response->assertViewIs('time-logs.create');
        // check if clock-in button is not shown
        $response->assertDontSee(url(route('time-logs.clock-in')));
    }
}
