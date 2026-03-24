<?php

namespace Tests\Feature\Http\Middleware;

use App\Agent;
use App\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('auth:player')->get('/test-auth-player', function () {
            return response('OK', 200);
        });

        Route::middleware('auth:admin')->get('/test-auth-admin', function () {
            return response('OK', 200);
        });
    }

    // -------------------------------------------------------------------------
    // Player guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_player_is_redirected_to_go(): void
    {
        $response = $this->get('/test-auth-player');

        $response->assertRedirect('go');
    }

    public function test_authenticated_player_is_allowed_through(): void
    {
        $player = Player::factory()->create();

        $response = $this->actingAs($player, 'player')
            ->get('/test-auth-player');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_player_ajax_request_returns_401(): void
    {
        $response = $this->getJson('/test-auth-player');

        $response->assertStatus(401);
        $response->assertSee('Unauthorized.');
    }

    // -------------------------------------------------------------------------
    // Admin guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_admin_is_redirected_to_login(): void
    {
        $response = $this->get('/test-auth-admin');

        $response->assertRedirect('login');
    }

    public function test_authenticated_admin_is_allowed_through(): void
    {
        $admin = Agent::factory()->create(['admin' => true]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/test-auth-admin');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_admin_ajax_request_returns_401(): void
    {
        $response = $this->getJson('/test-auth-admin');

        $response->assertStatus(401);
        $response->assertSee('Unauthorized.');
    }
}
