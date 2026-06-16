<?php

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => true,
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'mario@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Mario', $user->name);
        $this->assertEquals('Rossi', $user->surname);
        $this->assertEquals(UserType::USER, $user->type);
        $this->assertEquals('web', $user->platform);
        $this->assertEquals(0, $user->credits);
    }

    public function test_registration_creates_user_profile(): void
    {
        $this->post('/register', [
            'name' => 'Luca',
            'surname' => 'Bianchi',
            'email' => 'luca@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => true,
        ]);

        $user = User::where('email', 'luca@example.com')->first();
        $this->assertNotNull($user->profile);
    }

    public function test_registration_requires_surname(): void
    {
        $response = $this->post('/register', [
            'name' => 'Mario',
            'email' => 'mario@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => true,
        ]);

        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => true,
        ]);

        $this->assertGuest();
    }
}
