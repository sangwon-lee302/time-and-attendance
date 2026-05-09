<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_view_is_shown_correctly(): void
    {
        $response = $this->actingAs(User::factory()->unverified()->create())
            ->get(route('verification.notice'));

        $response->assertOk();
        $response->assertViewIs('staffs.verify-email');
    }

    public function test_users_can_jump_to_mailpit_dashboard(): void
    {
        $response = $this->actingAs(User::factory()->unverified()->create())
            ->get(route('verification.notice'));

        $response->assertOk();
        $response->assertSee(config('mail.mailpit_url'));
    }

    public function test_verification_email_is_resent_correctly(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'))->assertOk();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_users_are_redirected_to_email_verification_view_after_authentication(): void
    {
        $this->get('/login')->assertOk();

        $user = User::factory()->unverified()->create();

        $response = $this->followingRedirects()->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertViewIs('staffs.verify-email');
    }
}
