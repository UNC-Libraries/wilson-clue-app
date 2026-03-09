<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\GhostDna;
use App\Player;
use App\Quest;
use App\Question;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_view_with_team_and_options(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);
        $player = Player::factory()->create();
        $team->players()->attach($player->id);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->get(route('admin.team.edit', $team->id));

        $response->assertStatus(200);
        $response->assertViewIs('team.edit');
        $response->assertViewHas('team', fn($t) => $t->id === $team->id);
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
        $response->assertViewHas('academic_group_options');
        $response->assertViewHas('class_options');
    }

    public function test_edit_returns_404_for_nonexistent_team(): void
    {
        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->get(route('admin.team.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_changes_team_name_successfully(): void
    {
        $team = Team::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->put(route('admin.team.update', $team->id), [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('alert.message', 'Changes Saved!');
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_changes_dietary_restrictions(): void
    {
        $team = Team::factory()->create(['dietary' => 'None']);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->put(route('admin.team.update', $team->id), [
                'name' => $team->name,
                'dietary' => 'Vegetarian',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'dietary' => 'Vegetarian',
        ]);
    }

    public function test_update_changes_bonus_points(): void
    {
        $team = Team::factory()->create(['bonus_points' => 0]);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->put(route('admin.team.update', $team->id), [
                'name' => $team->name,
                'bonus_points' => 10,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'bonus_points' => 10,
        ]);
    }

    public function test_update_requires_name(): void
    {
        $team = Team::factory()->create();

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->put(route('admin.team.update', $team->id), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_only_changes_provided_attributes(): void
    {
        $team = Team::factory()->create([
            'name' => 'Original Name',
            'dietary' => 'Original Dietary',
            'bonus_points' => 5,
        ]);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->put(route('admin.team.update', $team->id), [
                'name' => 'Updated Name',
            ]);

        $fresh = $team->fresh();
        $this->assertEquals('Updated Name', $fresh->name);
        $this->assertEquals('Original Dietary', $fresh->dietary);
        $this->assertEquals(5, $fresh->bonus_points);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_team_and_detaches_relationships(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'name' => 'Test Team']);

        $player = Player::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);
        $question = Question::factory()->create();
        $dna = GhostDna::factory()->create();

        $team->players()->attach($player->id);
        $team->completedQuests()->attach($quest->id);
        $team->correctQuestions()->attach($question->id);
        $team->foundDna()->attach($dna->id);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->delete(route('admin.team.destroy', $team->id));

        $response->assertRedirect(route('admin.game.teams', ['id' => $game->id]));
        $response->assertSessionHas('alert.message', 'Test Team  deleted!');
        $response->assertSessionHas('alert.type', 'warning');

        $this->assertSoftDeleted('teams', ['id' => $team->id]);

        $this->assertDatabaseMissing('player_team', [
            'team_id' => $team->id,
            'player_id' => $player->id,
        ]);

        $this->assertDatabaseMissing('quest_team', [
            'team_id' => $team->id,
            'quest_id' => $quest->id,
        ]);

        $this->assertDatabaseMissing('question_team', [
            'team_id' => $team->id,
            'question_id' => $question->id,
        ]);

        $this->assertDatabaseMissing('ghost_dna_team', [
            'team_id' => $team->id,
            'ghost_dna_id' => $dna->id,
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_team(): void
    {
        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->delete(route('admin.team.destroy', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // toggleWaitlist
    // -------------------------------------------------------------------------

    public function test_toggle_waitlist_moves_registered_team_to_waitlist(): void
    {
        $team = Team::factory()->create(['waitlist' => false, 'name' => 'Team Name']);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.toggleWaitlist', $team->id));

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', 'Team Name moved to the waitlist');

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'waitlist' => true,
        ]);
    }

    public function test_toggle_waitlist_moves_waitlisted_team_to_registered(): void
    {
        $team = Team::factory()->create(['waitlist' => true, 'name' => 'Team Name']);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.toggleWaitlist', $team->id));

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Team Name registered');

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'waitlist' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // addPlayer
    // -------------------------------------------------------------------------

    public function test_add_player_with_existing_onyen_attaches_player_to_team(): void
    {
        $game = Game::factory()->create(['students_only' => false]);
        $team = Team::factory()->create(['game_id' => $game->id]);
        $player = Player::factory()->create(['onyen' => 'testplayer']);

        $playerMock = $this->partialMock(Player::class, function ($mock) use ($player) {
            $mock->shouldReceive('updateFromOnyen')->once();
            $mock->shouldReceive('getWarnings')->andReturn([]);
        });

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.addPlayer', $team->id), [
                'onyen' => 'testplayer',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('player_team', [
            'team_id' => $team->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_add_player_with_new_onyen_creates_and_attaches_player(): void
    {
        $game = Game::factory()->create(['students_only' => false]);
        $team = Team::factory()->create(['game_id' => $game->id]);

        $this->partialMock(Player::class, function ($mock) {
            $mock->shouldReceive('updateFromOnyen')->once();
            $mock->shouldReceive('getWarnings')->andReturn([]);
        });

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.addPlayer', $team->id), [
                'onyen' => 'newplayer',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('players', ['onyen' => 'newplayer']);
    }

    public function test_add_player_with_warnings_redirects_back_with_errors(): void
    {
        $game = Game::factory()->create(['students_only' => false]);
        $team = Team::factory()->create(['game_id' => $game->id]);

        $this->partialMock(Player::class, function ($mock) {
            $mock->shouldReceive('updateFromOnyen')->once();
            $mock->shouldReceive('getWarnings')->andReturn(['enlist.add_player.onyen_not_found']);
        });

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.addPlayer', $team->id), [
                'onyen' => 'badplayer',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_add_player_manually_creates_player_with_password(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.addPlayer', $team->id), [
                'email' => 'manual@example.com',
                'password' => 'testpassword',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'class_code' => 'UGRD',
                'academic_group_code' => 'CAS',
            ]);

        $response->assertRedirect();

        $player = Player::where('email', 'manual@example.com')->first();
        $this->assertNotNull($player);
        $this->assertTrue($player->manual);
        $this->assertTrue(Hash::check('testpassword', $player->password));
        $this->assertEquals('manual@example.com', $player->onyen);

        $this->assertDatabaseHas('player_team', [
            'team_id' => $team->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_add_player_manually_requires_all_fields(): void
    {
        $team = Team::factory()->create();

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.addPlayer', $team->id), [
                'email' => 'test@example.com',
            ]);

        $response->assertSessionHasErrors(['password', 'first_name', 'last_name', 'class_code', 'academic_group_code']);
    }

    public function test_add_player_with_override_non_student_flag(): void
    {
        $game = Game::factory()->create(['students_only' => false]);
        $team = Team::factory()->create(['game_id' => $game->id]);

        $this->partialMock(Player::class, function ($mock) {
            $mock->shouldReceive('updateFromOnyen')->with('testplayer', true)->once();
            $mock->shouldReceive('getWarnings')->andReturn([]);
        });

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.addPlayer', $team->id), [
                'onyen' => 'testplayer',
                'override_non_student' => '1',
            ]);

        $response->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // removePlayer
    // -------------------------------------------------------------------------

    public function test_remove_player_detaches_player_from_team(): void
    {
        $team = Team::factory()->create();
        $player = Player::factory()->create();

        $team->players()->attach($player->id);

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.removePlayer', ['id' => $team->id, 'playerId' => $player->id]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('player_team', [
            'team_id' => $team->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_remove_player_returns_404_for_nonexistent_team(): void
    {
        $player = Player::factory()->create();

        $response = $this->actingAs(\App\Agent::factory()->create(['admin' => true]), 'admin')
            ->post(route('admin.team.removePlayer', ['id' => 999999, 'playerId' => $player->id]));

        $response->assertStatus(404);
    }
}

