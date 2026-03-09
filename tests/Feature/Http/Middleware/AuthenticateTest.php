<?php

namespace Tests\Feature\Http\Middleware;

use App\Admin;
use App\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Player guard
    // -------------------------------------------------------------------------

    
    public function unauthenticated_player_is_redirected_to_go(): void
    {
        $response = $this->get(route('ui.index'));

        $response->assertRedirect('go');
    }

    
    public function test_authenticated_player_is_allowed_through(): void
    {
        $player = Player::factory()->create();

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => 1])
            ->get(route('ui.index'));

        $response->assertStatus(200);
    }

    
    public function test_unauthenticated_player_ajax_request_returns_401(): void
    {
        $response = $this->getJson(route('ui.index'));

        $response->assertStatus(401);
        $response->assertSee('Unauthorized.');
    }

    
    public function test_unauthenticated_player_wanting_json_returns_401(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get(route('ui.index'));

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Admin guard
    // -------------------------------------------------------------------------

    
    public function test_unauthenticated_admin_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect('login');
    }

    
    public function test_authenticated_admin_is_allowed_through(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.index'));

        $response->assertStatus(200);
    }

    
    public function test_unauthenticated_admin_ajax_request_returns_401(): void
    {
        $response = $this->getJson(route('admin.index'));

        $response->assertStatus(401);
        $response->assertSee('Unauthorized.');
    }

    
    public function test_unauthenticated_admin_wanting_json_returns_401(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get(route('admin.index'));

        $response->assertStatus(401);
    }
}

