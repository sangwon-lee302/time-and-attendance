<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_view_can_be_rendered(): void
    {
        $response = $this->get('admin/login');

        $response->assertOk();
        $response->assertSee('管理者ログイン');
        $response->assertSee('管理者ログインする');
        $response->assertSee(route('admin.login'));
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

        $this->get('admin/login')->assertOk();

        $response = $this->post(route('admin.login'), [
            'email'    => $user->email,
            'password' => '',
        ]);

        $this->assertGuest();

        $response->assertRedirect('admin/login');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_admin_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->admin()->create();

        $this->get('admin/login')->assertOk();

        $response = $this->post(route('admin.login'), [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertRedirect('admin/login');
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function test_admin_user_can_login_with_valid_credentials(): void
    {
        $password = 'password123';

        $user = User::factory()->admin()->create(['password' => $password]);

        $this->get('admin/login')->assertOk();

        $response = $this->post(route('admin.login'), [
            'email'    => $user->email,
            'password' => $password,
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('admin/attendance/list');
    }
}
