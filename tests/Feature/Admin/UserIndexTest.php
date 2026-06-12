<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_index_page_can_be_rendered(): void
    {
        $users = User::factory(5)->create();

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('admin/staff/list');

        $response->assertOk();
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
            $response->assertSee(
                'href="'.url('admin/attendance/staff/'.$user->id).'"',
                false,
            );
        }
    }
}
