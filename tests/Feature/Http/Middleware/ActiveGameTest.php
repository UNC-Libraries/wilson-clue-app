<?php

namespace Tests\Feature\Http\Middleware;

use App\Game;
use App\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ActiveGameTest extends TestCase
{
    use RefreshDatabase;

    private Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Player $player */
        $player = Player::factory()->create();
        $this->player = $player;

        Route::middleware(['web', 'auth:player', 'activeGame'])
            ->get('/test-active-game-middleware', function () {
                return response('OK', 200);
            });
    }

    
    public function test_it_passes_through_when_game_id_already_in_session(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $game->id])
            ->get('/test-active-game-middleware');

        $response->assertStatus(200);
    }

    
    public function test_it_sets_game_id_in_session_when_missing_but_active_game_exists(): void
    {
        $game = Game::factory()->inProgress()->create();

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-active-game-middleware');

        $response->assertSessionHas('gameId', $game->id);
    }

    
    public function test_it_redirects_to_logout_when_no_game_id_in_session_and_no_active_game(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->get('/test-active-game-middleware');

        $response->assertRedirect(route('player.logout'));
    }

    
    public function test_it_redirects_to_logout_when_all_games_are_inactive(): void
    {
        Game::factory()->inactive()->create();

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-active-game-middleware');

        $response->assertRedirect(route('player.logout'));
    }

    
    public function test_it_uses_most_recently_active_game_when_multiple_active_games_exist(): void
    {
        Game::factory()->inProgress()->create();
        Game::factory()->inProgress()->create();

        $expected = Game::query()->active()->first();

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-active-game-middleware');

        $response->assertSessionHas('gameId', $expected->id);
    }

    
    public function test_it_does_not_overwrite_existing_game_id_in_session(): void
    {
        $sessionGame = Game::factory()->create();
        Game::factory()->inProgress()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $sessionGame->id])
            ->get('/test-active-game-middleware');

        $response->assertSessionHas('gameId', $sessionGame->id);
    }
}

