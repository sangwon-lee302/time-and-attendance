<?php

namespace Tests\Feature\TimeLog;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

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
        $this->travelTo($randomDate);

        $response = $this->actingAs(User::factory()->create())->get('attendance');

        $response->assertOk();
        $response->assertSeeText('勤務外');
        $response->assertSeeText($randomDate->isoFormat('ll(ddd)'));
        $response->assertSeeText($randomDate->format('H:i'));
        // check if clock-in button is shown
        $response->assertSee(url(route('time-logs.clock-in')));
    }
}
