<?php

namespace Tests\Feature\TimeLog;

use App\Models\Attendance;
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

        $response->assertSeeText($randomDate->isoFormat('YYYY年M月D日(ddd)'));
        $response->assertSeeText($randomDate->format('H:i'));
    }

    public function test_status_can_be_resolved_for_a_user_not_clocked_in_yet(): void
    {
        $user = User::factory()->create();
        Attendance::factory()->recycle($user)->today()->notClockedOut()->create();

        $this->actingAs($user)->get('attendance')->assertSee('出勤中');
    }

    public function test_status_can_be_resolved_for_a_user_taking_a_break(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->today()
            ->hasNonOverlappingBreakTimes(1, false)
            ->create();

        $this->actingAs($user)->get('attendance')->assertSee('休憩中');
    }

    public function test_status_can_be_resolved_for_a_clocked_out_user(): void
    {
        $user = User::factory()->create();
        Attendance::factory()->recycle($user)->today()->create();

        $this->actingAs($user)->get('attendance')->assertSee('退勤済');
    }
}
