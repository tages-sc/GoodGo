<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_registration_is_throttled_per_ip(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', $this->webPayload("spam{$i}@example.com"));
            auth()->logout();
        }

        $this->post('/register', $this->webPayload('spam3@example.com'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 3);
    }

    public function test_api_signup_is_throttled_per_ip(): void
    {
        config(['services.api.secret_key' => 'test-key']);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/signup', $this->apiPayload("api{$i}@example.com"), ['secret-key' => 'test-key'])
                ->assertSuccessful();
        }

        $this->postJson('/api/v1/signup', $this->apiPayload('api3@example.com'), ['secret-key' => 'test-key'])
            ->assertStatus(429);
    }

    public function test_failed_validation_does_not_count(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', ['email' => 'not-an-email']);
        }

        $this->post('/register', $this->webPayload('ok@example.com'))
            ->assertSessionHasNoErrors();
    }

    private function webPayload(string $email): array
    {
        return [
            'name' => 'Spam',
            'surname' => 'Bot',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ];
    }

    private function apiPayload(string $email): array
    {
        return [
            'email' => $email,
            'password' => 'password',
            'first_name' => 'Spam',
            'last_name' => 'Bot',
            'privacy_accepted' => true,
            'terms_accepted' => true,
        ];
    }
}
