<?php

namespace Tests\Feature\Attendances;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock user instance.
     *
     * @var User
     */
    protected $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_this_months_attendances_can_be_shown(): void
    {
        Attendance::factory(20)
            ->recycle($this->user)
            ->uniqueInMonth(now()->format('Y-m'))
            ->hasNonOverlappingBreakTimes()
            ->create();

        $response = $this->actingAs($this->user)->get('attendance/list');

        $response->assertOk();
        foreach (Attendance::whereBetween('date', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])
            ->get() as $attendance) {
            $response->assertSee($attendance->clocked_in_at->format('H:i'));
            $response->assertSee($attendance->clocked_out_at->format('H:i'));
            $response->assertSee($attendance->total_break_time->format('%h:%I'));
            $response->assertSee($attendance->total_working_time->format('%h:%I'));
        }
    }

    public function test_todays_month_can_be_shown(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->get('attendance/list')
            ->assertSee(now()->format('Y/m'));
    }

    public function test_previous_months_attendances_can_be_shown(): void
    {
        $this->user    = User::factory()->create();
        $previousMonth = now()->subMonth()->format('Y-m');
        Attendance::factory(20)
            ->recycle($this->user)
            ->uniqueInMonth($previousMonth)
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this->actingAs($this->user)->get('attendance/list')->assertSee(
            'href="'.url('attendance/list?month='.$previousMonth).'"',
            false,
        );

        $response = $this
            ->actingAs($this->user)
            ->get('attendance/list?month='.$previousMonth);

        foreach (Attendance::whereBetween('date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])
            ->get() as $attendance) {
            $response->assertSee($attendance->clocked_in_at->format('H:i'));
            $response->assertSee($attendance->clocked_out_at->format('H:i'));
            $response->assertSee($attendance->total_break_time->format('%h:%I'));
            $response->assertSee($attendance->total_working_time->format('%h:%I'));
        }
    }

    public function test_next_months_attendances_can_be_shown(): void
    {
        $this->user = User::factory()->create();
        $nextMonth  = now()->addMonth()->format('Y-m');
        Attendance::factory(20)
            ->recycle($this->user)
            ->uniqueInMonth($nextMonth)
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this->actingAs($this->user)->get('attendance/list')->assertSee(
            'href="'.url('attendance/list?month='.$nextMonth).'"',
            false,
        );

        $response = $this
            ->actingAs($this->user)
            ->get('attendance/list?month='.$nextMonth);

        foreach (Attendance::whereBetween('date', [
            now()->addMonth()->startOfMonth(),
            now()->addMonth()->endOfMonth(),
        ])
            ->get() as $attendance) {
            $response->assertSee($attendance->clocked_in_at->format('H:i'));
            $response->assertSee($attendance->clocked_out_at->format('H:i'));
            $response->assertSee($attendance->total_break_time->format('%h:%I'));
            $response->assertSee($attendance->total_working_time->format('%h:%I'));
        }
    }

    public function test_user_can_jump_to_attendance_show_page(): void
    {
        $attendance = Attendance::factory()
            ->recycle($this->user)
            ->today()
            ->create();

        $this
            ->actingAs($this->user)
            ->get('attendance/list')
            ->assertSee(
                'href="'.url('attendance/detail/'.$attendance->id).'"',
                false,
            );

        $this
            ->actingAs($this->user)
            ->get('attendance/detail/'.$attendance->id)
            ->assertSee('勤怠詳細');
    }
}
