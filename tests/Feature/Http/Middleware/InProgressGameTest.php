<?php

namespace Tests\Feature\Http\Middleware;

use App\Game;
use App\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InProgressGameTest extends TestCase
{
    use RefreshDatabase;

    private Player $player;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var Player $player */
        $player = Player::factory()->create();
        $this->player = $player;

        Route::middleware(['web', 'auth:player', 'inProgressGame'])
            ->get('/test-in-progress-game-middleware', function () {
                return response('OK', 200);
            });
    }

    // -------------------------------------------------------------------------
    // override_in_progress session key
    // -------------------------------------------------------------------------

    
    public function test_it_uses_override_game_when_override_in_progress_session_key_is_set(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['override_in_progress' => $game->id])
            ->get('/test-in-progress-game-middleware');

        $response->assertStatus(200);
        $response->assertSessionHas('gameId', $game->id);
    }

    
    public function test_it_returns_404_when_override_game_id_does_not_exist(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession(['override_in_progress' => 9999])
            ->get('/test-in-progress-game-middleware');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // normal active()->inProgress() path
    // -------------------------------------------------------------------------

    
    public function test_it_passes_through_when_an_active_in_progress_game_exists(): void
    {
        Game::factory()->inProgress()->create();

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-in-progress-game-middleware');

        $response->assertStatus(200);
    }

    
    public function test_it_sets_game_id_in_session_when_active_in_progress_game_exists_and_session_is_empty(): void
    {
        $game = Game::factory()->inProgress()->create();

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-in-progress-game-middleware');

        $response->assertSessionHas('gameId', $game->id);
    }

    
    public function test_it_does_not_overwrite_existing_game_id_in_session(): void
    {
        $sessionGame    = Game::factory()->create();
        Game::factory()->inProgress()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $sessionGame->id])
            ->get('/test-in-progress-game-middleware');

        $response->assertSessionHas('gameId', $sessionGame->id);
    }

    // -------------------------------------------------------------------------
    // redirect to gameover
    // -------------------------------------------------------------------------

    
    public function test_it_redirects_to_gameover_when_no_active_in_progress_game_exists(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->get('/test-in-progress-game-middleware');

        $response->assertRedirect(route('gameover'));
    }

    
    public function test_it_redirects_to_gameover_when_game_is_active_but_not_in_progress(): void
    {
        Game::factory()->create([
            'active' => true,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-in-progress-game-middleware');

        $response->assertRedirect(route('gameover'));
    }

    
    public function test_it_redirects_to_gameover_when_game_is_in_progress_but_not_active(): void
    {
        Game::factory()->create([
            'active' => false,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->player, 'player')
            ->get('/test-in-progress-game-middleware');

        $response->assertRedirect(route('gameover'));
    }
}

