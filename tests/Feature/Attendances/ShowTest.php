<?php

namespace Tests\Feature\Attendances;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A mock user instance.
     * 
     * @var User
     */
    protected $user;

    /**
     * A mock attendance instance associated with the mock user.
     * 
     * @var Attendance
     */
    protected $attendance;

    #[Override]
    function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()
            ->recycle($this->user)
            ->hasNonOverlappingBreakTimes()
            ->create();
    }

    public function test_attended_user_name_can_be_shown(): void
    {
        $this
            ->actingAs($this->user)
            ->get('attendance/detail/'.$this->attendance->id)
            ->assertSeeInOrder([
                '名前',
                $this->user->name,    
            ]);
    }

    public function test_attended_date_can_be_shown(): void
    {
        $this
            ->actingAs($this->user)
            ->get('attendance/detail/'.$this->attendance->id)
            ->assertSeeInOrder([
                '日付',
                $this->attendance->date->format('Y年'),    
                $this->attendance->date->format('n月j日'),    
            ]);
    }

    public function test_clock_in_and_out_time_can_be_shown(): void
    {
        $this
            ->actingAs($this->user)
            ->get('attendance/detail/'.$this->attendance->id)
            ->assertSeeInOrder([
                '出勤・退勤',
                $this->attendance->clocked_in_at->format('H:i'),    
                $this->attendance->clocked_out_at->format('H:i'),    
            ]);
    }

    public function test_breaks_can_be_shown(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('attendance/detail/'.$this->attendance->id);

        foreach ($this->attendance->breakTimes as $breakTime) {
            $response->assertSee($breakTime->started_at->format('H:i'));
            $response->assertSee($breakTime->ended_at->format('H:i'));
        }
    }
}
