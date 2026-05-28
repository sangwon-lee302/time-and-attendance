<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_view_is_rendered_successfully(): void
    {
        $response = $this->get('admin/login');

        $response->assertOk();
        $response->assertSee('管理者ログイン');
        $response->assertSee('管理者ログインする');
        $response->assertSee(url(route('admin.login')));
    }

    public function test_admin_user_cannot_login_with_empty_email(): void
    {
        $password = 'password123';

        User::factory()->admin()->create(['password' => $password]);

        $this->get('admin/login')->assertOk();

        $response = $this->post(route('admin.login'), [
            'email'    => '',
            'password' => $password,
        ]);

        $this->assertGuest();

        $response->assertRedirect('admin/login');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_admin_user_cannot_login_with_empty_password(): void
    {
        $user = User::factory()->admin()->create();

        $this->get('login')->assertOk();

        $response = $this->post('login', [
            'email'    => $user->email,
            'password' => '',
        ]);

        $this->assertGuest();

        $response->assertRedirect('login');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_admin_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->admin()->create();

        $this->get('login')->assertOk();

        $response = $this->post('login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertRedirect('login');
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function test_admin_user_can_login_with_valid_credentials(): void
    {
        $password = 'password123';

        $user = User::factory()->admin()->create(['password' => $password]);

        $this->get('login')->assertOk();

        $response = $this->post('login', [
            'email'    => $user->email,
            'password' => $password,
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('admin/attendance/list');
    }
}
