<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_view_is_shown_correctly(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_staff_cannot_register_with_empty_name(): void
    {
        $user     = User::factory()->make();
        $password = 'password123';

        $this->get('/register')->assertOk();

        $response = $this->post('/register', [
            'name'                  => '',
            'email'                 => $user->email,
            'password'              => $password,
            'password_confirmation' => $password,
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    public function test_staff_cannot_register_with_empty_email(): void
    {
        $user     = User::factory()->make();
        $password = 'password123';

        $this->get('/register')->assertOk();

        $response = $this->post('/register', [
            'name'                  => $user->name,
            'email'                 => '',
            'password'              => $password,
            'password_confirmation' => $password,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => $user->name,
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_staff_cannot_register_with_empty_password(): void
    {
        $user = User::factory()->make();

        $this->get('/register')->assertOk();

        $response = $this->post('/register', [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'password'              => '',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_staff_cannot_register_with_unconfirmed_password(): void
    {
        $user = User::factory()->make();

        $this->get('/register')->assertOk();

        $response = $this->post('/register', [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'password'              => 'password123',
            'password_confirmation' => 'diff-password',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    public function test_staff_can_register_with_valid_credentials(): void
    {
        $user     = User::factory()->make();
        $password = 'password123';

        $this->get('/register')->assertOk();

        $response = $this->post('/register', [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'password'              => $password,
            'password_confirmation' => $password,
        ]);

        $registeredUser = User::whereEmail($user->email)->first();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);
        $this->assertAuthenticatedAs($registeredUser);

        $response->assertRedirect('/preview');
    }

    public function test_users_can_jump_to_login_page(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('/login');
    }
}
