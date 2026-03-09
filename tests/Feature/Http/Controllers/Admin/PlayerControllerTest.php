<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\Player;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlayerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_displays_all_players(): void
    {
        $playerA = Player::factory()->create(['last_name' => 'Anderson']);
        $playerB = Player::factory()->create(['last_name' => 'Brown']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index'));

        $response->assertStatus(200);
        $response->assertViewIs('player.index');
        $response->assertViewHas('players', function ($players) use ($playerA, $playerB) {
            return $players->pluck('id')->contains($playerA->id)
                && $players->pluck('id')->contains($playerB->id);
        });
    }

    public function test_index_sorts_by_last_name_ascending_by_default(): void
    {
        $playerZ = Player::factory()->create(['last_name' => 'Zimmerman']);
        $playerA = Player::factory()->create(['last_name' => 'Anderson']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index'));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerA, $playerZ) {
            return $players->first()->id === $playerA->id
                && $players->last()->id === $playerZ->id;
        });
    }

    public function test_index_sorts_by_last_name_descending(): void
    {
        $playerA = Player::factory()->create(['last_name' => 'Anderson']);
        $playerZ = Player::factory()->create(['last_name' => 'Zimmerman']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['sort_by' => 'last_name', 'sort_order' => 'desc']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerA, $playerZ) {
            return $players->first()->id === $playerZ->id
                && $players->last()->id === $playerA->id;
        });
    }

    public function test_index_sorts_by_onyen(): void
    {
        $playerB = Player::factory()->create(['onyen' => 'buser']);
        $playerA = Player::factory()->create(['onyen' => 'auser']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['sort_by' => 'onyen', 'sort_order' => 'asc']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerA, $playerB) {
            return $players->first()->id === $playerA->id
                && $players->last()->id === $playerB->id;
        });
    }

    public function test_index_sorts_by_team_count_ascending(): void
    {
        $playerWithNoTeams = Player::factory()->create();
        $playerWithTwoTeams = Player::factory()->create();

        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $playerWithTwoTeams->teams()->attach([$team1->id, $team2->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['sort_by' => 'team', 'sort_order' => 'asc']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerWithNoTeams, $playerWithTwoTeams) {
            return $players->first()->id === $playerWithNoTeams->id
                && $players->last()->id === $playerWithTwoTeams->id;
        });
    }

    public function test_index_sorts_by_team_count_descending(): void
    {
        $playerWithNoTeams = Player::factory()->create();
        $playerWithTwoTeams = Player::factory()->create();

        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $playerWithTwoTeams->teams()->attach([$team1->id, $team2->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['sort_by' => 'team', 'sort_order' => 'desc']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerWithNoTeams, $playerWithTwoTeams) {
            return $players->first()->id === $playerWithTwoTeams->id
                && $players->last()->id === $playerWithNoTeams->id;
        });
    }

    public function test_index_filters_by_game(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();

        $teamA = Team::factory()->create(['game_id' => $gameA->id]);
        $teamB = Team::factory()->create(['game_id' => $gameB->id]);

        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $playerA->teams()->attach($teamA->id);
        $playerB->teams()->attach($teamB->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['game' => [$gameA->id]]));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerA, $playerB) {
            return $players->pluck('id')->contains($playerA->id)
                && !$players->pluck('id')->contains($playerB->id);
        });
    }

    public function test_index_filters_by_multiple_games(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();
        $gameC = Game::factory()->create();

        $teamA = Team::factory()->create(['game_id' => $gameA->id]);
        $teamB = Team::factory()->create(['game_id' => $gameB->id]);
        $teamC = Team::factory()->create(['game_id' => $gameC->id]);

        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();
        $playerC = Player::factory()->create();

        $playerA->teams()->attach($teamA->id);
        $playerB->teams()->attach($teamB->id);
        $playerC->teams()->attach($teamC->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['game' => [$gameA->id, $gameB->id]]));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playerA, $playerB, $playerC) {
            return $players->pluck('id')->contains($playerA->id)
                && $players->pluck('id')->contains($playerB->id)
                && !$players->pluck('id')->contains($playerC->id);
        });
    }

    public function test_index_filters_by_class_code(): void
    {
        $undergrad = Player::factory()->create(['class_code' => 'UGRD']);
        $grad = Player::factory()->create(['class_code' => 'GRAD']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['class' => ['UGRD']]));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($undergrad, $grad) {
            return $players->pluck('id')->contains($undergrad->id)
                && !$players->pluck('id')->contains($grad->id);
        });
    }

    public function test_index_filters_by_academic_group_code(): void
    {
        $cas = Player::factory()->create(['academic_group_code' => 'CAS']);
        $som = Player::factory()->create(['academic_group_code' => 'SOM']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['group' => ['CAS']]));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($cas, $som) {
            return $players->pluck('id')->contains($cas->id)
                && !$players->pluck('id')->contains($som->id);
        });
    }

    public function test_index_filters_by_played_yes(): void
    {
        $playedPlayer = Player::factory()->create(['checked_in' => true]);
        $notPlayedPlayer = Player::factory()->create(['checked_in' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['played' => 'yes']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playedPlayer, $notPlayedPlayer) {
            return $players->pluck('id')->contains($playedPlayer->id)
                && !$players->pluck('id')->contains($notPlayedPlayer->id);
        });
    }

    public function test_index_filters_by_played_no(): void
    {
        $playedPlayer = Player::factory()->create(['checked_in' => true]);
        $notPlayedPlayer = Player::factory()->create(['checked_in' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['played' => 'no']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($playedPlayer, $notPlayedPlayer) {
            return !$players->pluck('id')->contains($playedPlayer->id)
                && $players->pluck('id')->contains($notPlayedPlayer->id);
        });
    }

    public function test_index_filters_by_non_student(): void
    {
        $student = Player::factory()->create(['student' => true]);
        $nonStudent = Player::factory()->create(['student' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['non_student' => '1']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($student, $nonStudent) {
            return !$players->pluck('id')->contains($student->id)
                && $players->pluck('id')->contains($nonStudent->id);
        });
    }

    public function test_index_filters_by_manual(): void
    {
        $ldapPlayer = Player::factory()->create(['manual' => false]);
        $manualPlayer = Player::factory()->create(['manual' => true]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['manual' => '1']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($ldapPlayer, $manualPlayer) {
            return !$players->pluck('id')->contains($ldapPlayer->id)
                && $players->pluck('id')->contains($manualPlayer->id);
        });
    }

    public function test_index_searches_by_first_name(): void
    {
        $john = Player::factory()->create(['first_name' => 'john']);
        $jane = Player::factory()->create(['first_name' => 'jane']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['search' => 'john']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($john, $jane) {
            return $players->pluck('id')->contains($john->id)
                && !$players->pluck('id')->contains($jane->id);
        });
    }

    public function test_index_searches_by_last_name(): void
    {
        $smith = Player::factory()->create(['last_name' => 'smith']);
        $jones = Player::factory()->create(['last_name' => 'jones']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['search' => 'smith']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($smith, $jones) {
            return $players->pluck('id')->contains($smith->id)
                && !$players->pluck('id')->contains($jones->id);
        });
    }

    public function test_index_searches_by_pid(): void
    {
        $player1 = Player::factory()->create(['pid' => '123456789']);
        $player2 = Player::factory()->create(['pid' => '987654321']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['search' => '123456']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($player1, $player2) {
            return $players->pluck('id')->contains($player1->id)
                && !$players->pluck('id')->contains($player2->id);
        });
    }

    public function test_index_searches_by_email(): void
    {
        $player1 = Player::factory()->create(['email' => 'john@example.com']);
        $player2 = Player::factory()->create(['email' => 'jane@example.com']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['search' => 'john@']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($player1, $player2) {
            return $players->pluck('id')->contains($player1->id)
                && !$players->pluck('id')->contains($player2->id);
        });
    }

    public function test_index_searches_by_onyen(): void
    {
        $player1 = Player::factory()->create(['onyen' => 'jsmith']);
        $player2 = Player::factory()->create(['onyen' => 'jjones']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['search' => 'jsmith']));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($player1, $player2) {
            return $players->pluck('id')->contains($player1->id)
                && !$players->pluck('id')->contains($player2->id);
        });
    }

    public function test_index_combines_multiple_filters(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $matchingPlayer = Player::factory()->create([
            'class_code' => 'UGRD',
            'checked_in' => false,
            'first_name' => 'john',
        ]);

        $nonMatchingPlayer = Player::factory()->create([
            'class_code' => 'GRAD',
            'checked_in' => false,
            'first_name' => 'jane',
        ]);

        $matchingPlayer->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', [
                'game' => [$game->id],
                'class' => ['UGRD'],
                'played' => 'no',
                'search' => 'john',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('players', function ($players) use ($matchingPlayer, $nonMatchingPlayer) {
            return $players->count() === 1
                && $players->first()->id === $matchingPlayer->id;
        });
    }

    public function test_index_handles_null_class_code_in_filter(): void
    {
        $nullClassPlayer = Player::factory()->create(['class_code' => null]);
        $validClassPlayer = Player::factory()->create(['class_code' => 'UGRD']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['class' => [null]]));

        $response->assertStatus(200);
    }

    public function test_index_handles_null_academic_group_in_filter(): void
    {
        $nullGroupPlayer = Player::factory()->create(['academic_group_code' => null]);
        $validGroupPlayer = Player::factory()->create(['academic_group_code' => 'CAS']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index', ['group' => [null]]));

        $response->assertStatus(200);
    }

    public function test_index_provides_sort_options_to_view(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sortOptions');
        $response->assertViewHas('selectedSort', 'last_name');
        $response->assertViewHas('sortOrder');
        $response->assertViewHas('selectedSortOrder', 'asc');
    }

    public function test_index_provides_class_and_academic_group_options_to_view(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.player.index'));

        $response->assertStatus(200);
        $response->assertViewHas('class_options');
        $response->assertViewHas('academic_group_options');
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_player_with_teams_and_games(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);
        $player = Player::factory()->create();

        $player->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.player.edit', $player->id));

        $response->assertStatus(200);
        $response->assertViewIs('player.edit');
        $response->assertViewHas('player', function ($p) use ($player, $team, $game) {
            return $p->id === $player->id
                && $p->teams->pluck('id')->contains($team->id)
                && $p->teams->first()->game->id === $game->id;
        });
    }

    public function test_edit_returns_404_for_nonexistent_player(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.player.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_player_attributes(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'original',
            'last_name' => 'name',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.player.update', $player->id), [
                'first_name' => 'updated',
                'last_name' => 'player',
            ]);

        $response->assertRedirect(route('admin.player.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', function ($message) {
            return str_contains($message, 'updated');
        });

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'first_name' => 'updated',
            'last_name' => 'player',
        ]);
    }

    public function test_update_sets_checked_in_to_false(): void
    {
        $player = Player::factory()->create(['checked_in' => true]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.player.update', $player->id), [
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
            ]);

        $response->assertRedirect(route('admin.player.index'));

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'checked_in' => false,
        ]);
    }

    public function test_update_with_password_updates_password_and_onyen(): void
    {
        $player = Player::factory()->create([
            'onyen' => 'olduser',
            'email' => 'newemail@example.com',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.player.update', $player->id), [
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                'email' => 'newemail@example.com',
                'password' => 'newpassword123',
            ]);

        $response->assertRedirect(route('admin.player.index'));

        $fresh = $player->fresh();
        $this->assertEquals('newemail@example.com', $fresh->onyen);
        $this->assertTrue(Hash::check('newpassword123', $fresh->password));
    }

    public function test_update_without_password_does_not_modify_onyen_or_password(): void
    {
        $player = Player::factory()->create([
            'onyen' => 'originaluser',
            'password' => Hash::make('originalpassword'),
            'email' => 'original@example.com',
        ]);

        $originalPassword = $player->password;

        $response = $this->actingAsAdmin()
            ->put(route('admin.player.update', $player->id), [
                'first_name' => 'updated',
                'last_name' => $player->last_name,
                'email' => 'newemail@example.com',
            ]);

        $response->assertRedirect(route('admin.player.index'));

        $fresh = $player->fresh();
        $this->assertEquals('originaluser', $fresh->onyen);
        $this->assertEquals($originalPassword, $fresh->password);
        $this->assertEquals('newemail@example.com', $fresh->email);
    }

    public function test_update_returns_404_for_nonexistent_player(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.player.update', 999999), [
                'first_name' => 'Test',
                'last_name' => 'User',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_redirects_with_player_full_name_in_message(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'john',
            'last_name' => 'doe',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.player.update', $player->id), [
                'first_name' => 'john',
                'last_name' => 'doe',
            ]);

        $response->assertRedirect(route('admin.player.index'));
        $response->assertSessionHas('alert.message', 'John Doe updated');
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_player_and_detaches_teams(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'john',
            'last_name' => 'doe',
        ]);
        $team = Team::factory()->create();

        $player->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.player.destroy', $player->id));

        $response->assertRedirect(route('admin.player.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'John Doe removed');

        $this->assertDatabaseMissing('players', ['id' => $player->id]);
        $this->assertDatabaseMissing('player_team', [
            'player_id' => $player->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_destroy_deletes_player_with_multiple_teams(): void
    {
        $player = Player::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $player->teams()->attach([$team1->id, $team2->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.player.destroy', $player->id));

        $response->assertRedirect(route('admin.player.index'));

        $this->assertDatabaseMissing('players', ['id' => $player->id]);
        $this->assertDatabaseMissing('player_team', ['player_id' => $player->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_player(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.player.destroy', 999999));

        $response->assertStatus(404);
    }

    public function test_destroy_redirects_with_player_full_name_in_message(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'jane',
            'last_name' => 'smith',
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.player.destroy', $player->id));

        $response->assertRedirect(route('admin.player.index'));
        $response->assertSessionHas('alert.message', 'Jane Smith removed');
    }
}

