<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_view_is_shown_correctly(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_staff_cannot_login_with_empty_email(): void
    {
        $password = 'password123';

        User::factory()->create(['password' => $password]);

        $this->get('/login')->assertOk();

        $response = $this->post('/login', [
            'email'    => '',
            'password' => $password,
        ]);

        $this->assertGuest();

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_staff_cannot_login_with_empty_password(): void
    {
        $password = 'password123';

        $user = User::factory()->create(['password' => $password]);

        $this->get('/login')->assertOk();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => '',
        ]);

        $this->assertGuest();

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_staff_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->get('/login')->assertOk();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function test_staff_can_login_with_valid_credentials(): void
    {
        $password = 'password123';

        $user = User::factory()->create(['password' => $password]);

        $this->get('/login')->assertOk();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => $password,
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/preview');
    }

    public function test_users_can_jump_to_register_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('/register');
    }
}
