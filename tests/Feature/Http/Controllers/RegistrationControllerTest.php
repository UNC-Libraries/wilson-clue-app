<?php

namespace Tests\Feature\Http\Controllers;

use App\Game;
use App\Player;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    
    public function test_index_shows_enlist_view_when_registration_is_open(): void
    {
        $game = Game::factory()->create(['registration' => true]);

        $response = $this->withSession(['gameId' => $game->id])
            ->get(route('enlist.index'));

        $response->assertStatus(200);
        $response->assertViewIs('web.registration.enlist');
        $response->assertViewHas('game');
    }

    
    public function test_index_redirects_to_home_when_registration_is_closed(): void
    {
        $game = Game::factory()->create(['registration' => false]);

        $response = $this->withSession(['gameId' => $game->id])
            ->get(route('enlist.index'));

        $response->assertRedirect('/');
    }

    
    public function test_index_returns_404_when_game_not_found(): void
    {
        $response = $this->withSession(['gameId' => 9999])
            ->get(route('enlist.index'));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // enlist
    // -------------------------------------------------------------------------

    
    public function test_enlist_requires_onyen_and_team_name(): void
    {
        $response = $this->post(route('enlist.enlist'), []);

        $response->assertSessionHasErrors(['onyen', 'teamName']);
    }

    
    public function test_enlist_redirects_back_when_no_active_game_with_registration(): void
    {
        Game::factory()->create(['registration' => false]);

        $response = $this->post(route('enlist.enlist'), [
            'onyen'    => 'testuser',
            'teamName' => 'Test Team',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    
    public function test_enlist_creates_team_and_redirects_to_team_management(): void
    {
        Mail::fake();

        $game = Game::factory()->create(['registration' => true]);

        $player = Player::factory()->create(['onyen' => 'testuser']);

        // Mock updateFromOnyen and getWarnings so we don't hit LDAP
        $playerMock = $this->partialMock(Player::class, function ($mock) {
            $mock->shouldReceive('updateFromOnyen')->andReturnNull();
            $mock->shouldReceive('getWarnings')->andReturn([]);
        });

        $response = $this->post(route('enlist.enlist'), [
            'onyen'    => $player->onyen,
            'teamName' => 'New Team',
        ]);

        $response->assertRedirect(route('enlist.teamManagement'));
        $this->assertDatabaseHas('teams', ['name' => 'New Team']);
    }

    // -------------------------------------------------------------------------
    // teamManagement
    // -------------------------------------------------------------------------

    
    public function test_team_management_redirects_to_logout_when_player_has_no_team(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->get(route('enlist.teamManagement'));

        $response->assertRedirect(route('player.logout'));
    }

    
    public function test_team_management_shows_view_for_registered_team(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();
        $team   = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $team->players()->attach($player);

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->get(route('enlist.teamManagement'));

        $response->assertStatus(200);
        $response->assertViewIs('web.registration.team_management');
    }

    
    public function test_team_management_shows_view_for_waitlisted_team(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();
        $team   = Team::factory()->create(['game_id' => $game->id, 'waitlist' => true]);
        $team->players()->attach($player);

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->get(route('enlist.teamManagement'));

        $response->assertStatus(200);
        $response->assertViewIs('web.registration.team_management');
    }

    // -------------------------------------------------------------------------
    // updateTeam
    // -------------------------------------------------------------------------

    
    public function test_update_team_requires_name(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->post(route('enlist.updateTeam'), []);

        $response->assertSessionHasErrors(['name']);
    }

    
    public function test_update_team_saves_new_name_and_redirects(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();
        $team   = Team::factory()->create(['game_id' => $game->id]);
        $team->players()->attach($player);

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->post(route('enlist.updateTeam'), ['name' => 'Updated Team Name']);

        $response->assertRedirect(route('enlist.teamManagement'));
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Updated Team Name']);
    }

    // -------------------------------------------------------------------------
    // addPlayer
    // -------------------------------------------------------------------------

    
    public function test_add_player_requires_onyen(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->post(route('enlist.addPlayer'), []);

        $response->assertSessionHasErrors(['onyen']);
    }

    
    public function test_add_player_redirects_back_when_team_is_full(): void
    {
        $game   = Game::factory()->create();
        $player = Player::factory()->create();
        $team   = Team::factory()->create(['game_id' => $game->id]);
        $others = Player::factory()->count(5)->create();
        $team->players()->attach(array_merge([$player->id], $others->pluck('id')->toArray()));

        $response = $this->actingAs($player, 'player')
            ->withSession(['gameId' => $game->id])
            ->post(route('enlist.addPlayer'), ['onyen' => 'newplayer']);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    // -------------------------------------------------------------------------
    // removePlayer
    // -------------------------------------------------------------------------


    public function test_remove_player_detaches_player_from_team_and_redirects(): void
    {
        $game      = Game::factory()->create();
        $leader    = Player::factory()->create();
        $toRemove  = Player::factory()->create();
        $team      = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $team->players()->attach([$leader->id, $toRemove->id]);

        $response = $this->actingAs($leader, 'player')
            ->withSession(['gameId' => $game->id])
            ->delete(route('enlist.removePlayer', $toRemove->id));

        $response->assertRedirect(route('enlist.teamManagement'));
        $this->assertDatabaseMissing('player_team', [
            'team_id'   => $team->id,
            'player_id' => $toRemove->id,
        ]);
    }

    
    public function test_remove_player_sets_team_to_waitlist_when_below_minimum(): void
    {
        $game     = Game::factory()->create();
        $leader   = Player::factory()->create();
        $toRemove = Player::factory()->create();
        $team     = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        // Attach only minimum players so removing one drops below minimum
        $team->players()->attach([$leader->id, $toRemove->id]);

        $this->actingAs($leader, 'player')
            ->withSession(['gameId' => $game->id])
            ->delete(route('enlist.removePlayer', $toRemove->id));

        $this->assertDatabaseHas('teams', ['id' => $team->id, 'waitlist' => true]);
    }
}

