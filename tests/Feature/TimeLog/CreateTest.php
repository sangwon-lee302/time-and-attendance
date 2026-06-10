<?php

namespace Tests\Feature\TimeLog;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_log_create_view_can_be_rendered(): void
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
        $response->assertSeeText($randomDate->isoFormat('YYYY年M月D日(ddd)'));
        $response->assertSeeText($randomDate->format('H:i'));
        // check if clock-in button is shown
        $response->assertSee(route('time-logs.clock-in'));
    }
}
