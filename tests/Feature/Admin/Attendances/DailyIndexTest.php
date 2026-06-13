<?php

namespace Tests\Feature\Admin\Attendances;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class DailyIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock admin user instance.
     *
     * @var User
     */
    protected $admin;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_daily_attendances_can_be_shown(): void
    {
        $users       = User::factory(10)->create();
        $attendances = [];
        foreach ($users as $user) {
            $attendances[] = Attendance::factory()
                ->recycle($user)
                ->ofDate()
                ->hasNonOverlappingBreakTimes()
                ->create();
        }

        $response = $this
            ->actingAs($this->admin)
            ->get('admin/attendance/list')
            ->assertOk();

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->clocked_in_at->format('H:i'));
            $response->assertSee($attendance->clocked_out_at->format('H:i'));
            $response->assertSee($attendance->total_break_time->format('%h:%I'));
            $response->assertSee($attendance->total_working_time->format('%h:%I'));
        }
    }

    public function test_todays_date_can_be_shown(): void
    {
        $user = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->ofDate()
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->actingAs($this->admin)
            ->get('admin/attendance/list')
            ->assertOk()
            ->assertSee(today()->format('Y/m/d'));
    }

    public function test_yesterdays_attendances_can_be_shown(): void
    {
        $yesterday = CarbonImmutable::yesterday();
        $user      = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->ofDate($yesterday)
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->actingAs($this->admin)
            ->get('admin/attendance/list?date='.$yesterday->format('Y-m-d'))
            ->assertOk()
            ->assertSee($yesterday->format('Y/m/d'));
    }

    public function test_tomorrows_attendances_can_be_shown(): void
    {
        $this->travelTo(today()->subWeek());

        $tomorrow = CarbonImmutable::tomorrow();
        $user     = User::factory()->create();
        Attendance::factory()
            ->recycle($user)
            ->ofDate($tomorrow)
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->actingAs($this->admin)
            ->get('admin/attendance/list?date='.$tomorrow->format('Y-m-d'))
            ->assertOk()
            ->assertSee($tomorrow->format('Y/m/d'));
    }
}
