<?php

namespace Tests\Feature\Http\Middleware;

use App\Agent;
use App\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register a test route protected by the middleware
        Route::middleware('player')->get('/test-player-middleware', function () {
            return response('OK', 200);
        });
    }

    
    public function test_unauthenticated_request_is_redirected_to_home(): void
    {
        $response = $this->get('/test-player-middleware');

        $response->assertRedirect('/');
    }

    
    public function test_authenticated_player_is_allowed_through(): void
    {
        $player = Player::factory()->create();

        $response = $this->actingAs($player, 'player')
            ->get('/test-player-middleware');

        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    
    public function test_unauthenticated_user_using_wrong_guard_is_redirected_to_home(): void
    {
        $player = Player::factory()->create();

        // Authenticated as web guard, not player guard
        $response = $this->actingAs($player, 'web')
            ->get('/test-player-middleware');

        $response->assertRedirect('/');
    }

    
    public function test_middleware_uses_player_guard_by_default(): void
    {
        $player = Player::factory()->create();

        Auth::guard('player')->login($player);

        $response = $this->get('/test-player-middleware');

        $response->assertStatus(200);
    }

    
    public function test_middleware_respects_custom_guard_when_specified(): void
    {
        Route::middleware('player:admin')->get('/test-admin-middleware', function () {
            return response('OK', 200);
        });

        $admin = Agent::factory()->create(['admin' => true]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/test-admin-middleware');

        $response->assertStatus(200);
    }

    
    public function test_unauthenticated_request_to_custom_guard_route_is_redirected_to_home(): void
    {
        Route::middleware('player:admin')->get('/test-admin-middleware', function () {
            return response('OK', 200);
        });

        $response = $this->get('/test-admin-middleware');

        $response->assertRedirect('/');
    }
}
