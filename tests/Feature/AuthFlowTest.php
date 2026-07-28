<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_can_register_and_is_authenticated(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Listener',
            'username' => 'testlistener',
            'email' => 'listener@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'account_type' => 'listener',
            'terms' => '1',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'listener@example.test', 'role' => 'listener']);
    }

    public function test_blocked_user_cannot_login(): void
    {
        User::create([
            'name' => 'Blocked', 'username' => 'blocked', 'email' => 'blocked@example.test',
            'password' => 'Password123!', 'role' => 'listener', 'is_active' => false,
        ]);

        $this->post('/login', ['login' => 'blocked@example.test', 'password' => 'Password123!'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
