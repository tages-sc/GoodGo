<?php

namespace Tests\Feature\Middleware;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTypeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function createUserOfType(UserType $type): User
    {
        return User::factory()->create(['type' => $type]);
    }

    public function test_super_admin_can_access_admin_routes(): void
    {
        $admin = $this->createUserOfType(UserType::SUPER_ADMIN);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = $this->createUserOfType(UserType::USER);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_partner_cannot_access_admin_routes(): void
    {
        $partner = $this->createUserOfType(UserType::PARTNER);

        $response = $this->actingAs($partner)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_ente_can_access_ente_routes(): void
    {
        $ente = $this->createUserOfType(UserType::ENTE);

        $response = $this->actingAs($ente)->get('/ente/competitions');
        $response->assertStatus(200);
    }

    public function test_user_cannot_access_ente_routes(): void
    {
        $user = $this->createUserOfType(UserType::USER);

        $response = $this->actingAs($user)->get('/ente/competitions');
        $response->assertStatus(403);
    }

    public function test_user_can_access_user_routes(): void
    {
        $user = $this->createUserOfType(UserType::USER);

        $response = $this->actingAs($user)->get('/user/competitions');
        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }
}
