<?php

namespace Tests\Feature\Http\Middleware;

use App\Game;
use App\Player;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ValidTeamTest extends TestCase
{
    use RefreshDatabase;

    private Player $player;
    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Player $player */
        $player = Player::factory()->create();
        $this->player = $player;

        /** @var Game $game */
        $game = Game::factory()->create();
        $this->game = $game;

        Route::middleware(['auth:player', 'validTeam'])->get('/test-valid-team-middleware', function () {
            return response('OK', 200);
        });
    }

    // -------------------------------------------------------------------------
    // teamId already in session
    // -------------------------------------------------------------------------

    
    public function test_it_passes_through_when_team_id_already_in_session(): void
    {
        $team = Team::factory()->create(['game_id' => $this->game->id]);
        $team->players()->attach($this->player);

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id, 'teamId' => $team->id])
            ->get('/test-valid-team-middleware');

        $response->assertStatus(200);
        $response->assertSee('OK');
        $response->assertSessionHas('teamId', $team->id);
    }

    
    public function test_it_does_not_query_for_team_when_team_id_already_in_session(): void
    {
        $team = Team::factory()->create(['game_id' => $this->game->id]);
        $team->players()->attach($this->player);

        // Player has no active team but teamId is already in session — should still pass
        /** @var Player $otherPlayer */
        $otherPlayer = Player::factory()->create();

        $response = $this->actingAs($otherPlayer, 'player')
            ->withSession(['gameId' => $this->game->id, 'teamId' => $team->id])
            ->get('/test-valid-team-middleware');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // teamId missing from session
    // -------------------------------------------------------------------------

    
    public function test_it_sets_team_id_in_session_when_missing_and_player_has_active_team(): void
    {
        $team = Team::factory()->registered()->create(['game_id' => $this->game->id]);
        $team->players()->attach($this->player);

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id])
            ->get('/test-valid-team-middleware');

        $response->assertStatus(200);
        $response->assertSessionHas('teamId', $team->id);
    }

    
    public function test_it_uses_first_active_team_when_player_belongs_to_multiple_teams(): void
    {
        $first  = Team::factory()->registered()->create(['game_id' => $this->game->id]);
        $second = Team::factory()->registered()->create(['game_id' => $this->game->id]);
        $first->players()->attach($this->player);
        $second->players()->attach($this->player);

        $expected = $this->player->teams()->active()->first();

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id])
            ->get('/test-valid-team-middleware');

        $response->assertSessionHas('teamId', $expected->id);
    }

    
    public function test_it_ignores_inactive_teams_when_setting_team_id(): void
    {
        $inactive = Team::factory()->waitlist()->create(['game_id' => $this->game->id]);
        $active   = Team::factory()->registered()->create(['game_id' => $this->game->id]);
        $inactive->players()->attach($this->player);
        $active->players()->attach($this->player);

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id])
            ->get('/test-valid-team-middleware');

        $response->assertSessionHas('teamId', $active->id);
    }

    // -------------------------------------------------------------------------
    // redirect to logout
    // -------------------------------------------------------------------------

    
    public function test_it_redirects_to_logout_when_player_has_no_teams(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id])
            ->get('/test-valid-team-middleware');

        $response->assertRedirect(route('player.logout'));
    }

    
    public function test_it_redirects_to_logout_when_player_has_no_active_teams(): void
    {
        $team = Team::factory()->waitlist()->create(['game_id' => $this->game->id]);
        $team->players()->attach($this->player);

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id])
            ->get('/test-valid-team-middleware');

        $response->assertRedirect(route('player.logout'));
    }

    
    public function test_it_redirects_to_logout_when_player_belongs_to_teams_in_different_game_only(): void
    {
        $otherGame = Game::factory()->create();
        $team      = Team::factory()->registered()->create(['game_id' => $otherGame->id]);
        $team->players()->attach($this->player);

        $response = $this->actingAs($this->player, 'player')
            ->withSession(['gameId' => $this->game->id])
            ->get('/test-valid-team-middleware');

        $response->assertRedirect(route('player.logout'));
    }
}

