<?php

namespace Tests\Feature\Admin\Users;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Override;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock admin user.
     *
     * @var User
     */
    protected $admin;

    /**
     * A mock regular user.
     *
     * @var User
     */
    protected $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user  = User::factory()->create();
    }

    public function test_users_information_can_be_shown(): void
    {
        $users = User::factory(10)->create();

        $response = $this
            ->actingAs($this->admin)
            ->get('admin/staff/list')
            ->assertOk();

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_this_months_attendance_information_can_be_shown(): void
    {
        Attendance::factory()
            ->recycle($this->user)
            ->uniqueInMonth()
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->accessToMonthlyIndexPage()
            ->assertOk();

        $this
            ->accessToMonthlyIndexPage()
            ->assertSee($this->user->name);
    }

    public function test_last_months_attendance_information_can_be_shown(): void
    {
        $lastMonth = now()->subMonth()->format('m');
        Attendance::factory()
            ->recycle($this->user)
            ->uniqueInMonth($lastMonth)
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->accessToMonthlyIndexPage($lastMonth)
            ->assertOk();

        $this
            ->accessToMonthlyIndexPage($lastMonth)
            ->assertSee($this->user->name);
    }

    public function test_next_months_attendance_information_can_be_shown(): void
    {
        $this->travelTo(now()->subWeek());

        $nextMonth = now()->addMonth()->format('m');
        Attendance::factory()
            ->recycle($this->user)
            ->uniqueInMonth($nextMonth)
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->accessToMonthlyIndexPage()
            ->assertOk();

        $this
            ->accessToMonthlyIndexPage($nextMonth)
            ->assertSee($this->user->name);
    }

    public function test_admin_can_jump_to_attendance_show_page(): void
    {
        $attendance = Attendance::factory()
            ->recycle($this->user)
            ->uniqueInMonth()
            ->hasNonOverlappingBreakTimes()
            ->create();

        $this
            ->accessToMonthlyIndexPage()
            ->assertOk();

        $this
            ->actingAs($this->admin)
            ->get('admin/attendance/'.$attendance->id)
            ->assertOk();
    }

    protected function accessToMonthlyIndexPage(
        ?string $month = null,
        ?string $year = null
    ): TestResponse {
        $month = $month ?? now()->format('m');
        $year  = $year ?? now()->format('Y');

        return $this
            ->actingAs($this->admin)
            ->get(
                'admin/attendance/staff/'.$this->user->id.'?month='.$year.'-'.$month,
            );
    }
}
