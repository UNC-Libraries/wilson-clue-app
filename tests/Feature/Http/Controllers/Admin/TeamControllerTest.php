<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\GhostDna;
use App\Player;
use App\Quest;
use App\Question;
use App\Suspect;
use App\Location;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Force an in-memory SQLite database before the application boots so
        // that RefreshDatabase can run migrate:fresh locally without the VM's
        // MySQL server being reachable.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE']   = ':memory:';

        parent::setUp();
    }

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

        $response = $this->actingAsAdmin()
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
        $response = $this->actingAsAdmin()
            ->get(route('admin.team.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_changes_team_name_successfully(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'name' => 'Original Name']);

        $response = $this->actingAsAdmin()
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
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'dietary' => 'None']);

        $response = $this->actingAsAdmin()
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
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'bonus_points' => 0]);

        $response = $this->actingAsAdmin()
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
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.team.update', $team->id), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_only_changes_provided_attributes(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create([
            'game_id'      => $game->id,
            'name'         => 'Original Name',
            'dietary'      => 'Original Dietary',
            'bonus_points' => 5,
        ]);

        $response = $this->actingAsAdmin()
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
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $team = Team::factory()->create([
            'game_id'     => $game->id,
            'name'        => 'Test Team',
            'suspect_id'  => $suspect->id,
            'location_id' => $location->id,
        ]);

        $player = Player::factory()->create();
        $quest = Quest::factory()->create([
            'game_id'     => $game->id,
            'suspect_id'  => $suspect->id,
            'location_id' => $location->id,
        ]);
        $question = Question::factory()->create();
        $dna = GhostDna::factory()->create();

        $team->players()->attach($player->id);
        $team->completedQuests()->attach($quest->id);
        $team->correctQuestions()->attach($question->id);
        $team->foundDna()->attach($dna->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.team.destroy', $team->id));

        $response->assertRedirect(route('admin.game.teams', ['id' => $game->id]));
        $response->assertSessionHas('alert.message', 'Test Team  deleted!');
        $response->assertSessionHas('alert.type', 'warning');

        $this->assertSoftDeleted('teams', ['id' => $team->id]);

        $this->assertDatabaseMissing('player_team', [
            'team_id'   => $team->id,
            'player_id' => $player->id,
        ]);

        $this->assertDatabaseMissing('quest_team', [
            'team_id'  => $team->id,
            'quest_id' => $quest->id,
        ]);

        $this->assertDatabaseMissing('question_team', [
            'team_id'     => $team->id,
            'question_id' => $question->id,
        ]);

        $this->assertDatabaseMissing('ghost_dna_team', [
            'team_id'     => $team->id,
            'ghost_dna_id' => $dna->id,
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_team(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.team.destroy', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // toggleWaitlist
    // -------------------------------------------------------------------------

    public function test_toggle_waitlist_moves_registered_team_to_waitlist(): void
    {
        // Route is admin.team.waitlist (not admin.team.toggleWaitlist)
        $team = Team::factory()->create(['waitlist' => false, 'name' => 'Team Name']);

        $response = $this->actingAsAdmin()
            ->post(route('admin.team.waitlist', $team->id));

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', 'Team Name moved to the waitlist');

        $this->assertDatabaseHas('teams', [
            'id'       => $team->id,
            'waitlist' => true,
        ]);
    }

    public function test_toggle_waitlist_moves_waitlisted_team_to_registered(): void
    {
        $team = Team::factory()->create(['waitlist' => true, 'name' => 'Team Name']);

        $response = $this->actingAsAdmin()
            ->post(route('admin.team.waitlist', $team->id));

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Team Name registered');

        $this->assertDatabaseHas('teams', [
            'id'       => $team->id,
            'waitlist' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // addPlayer — LDAP (onyen) path
    // These tests require a live or faked LDAP directory. The onyen path calls
    // $player->updateFromOnyen() and $player->validOnyen() which both issue
    // real LDAP queries that cannot be intercepted without a running directory
    // or a working LdapRecord DirectoryFake setup. They are commented out until
    // a suitable test LDAP environment is available.
    // -------------------------------------------------------------------------

    // public function test_add_player_with_valid_onyen_creates_and_attaches_player(): void
    // {
    //     $game = Game::factory()->create();
    //     $team = Team::factory()->create(['game_id' => $game->id]);
    //
    //     // Requires DirectoryFake to seed fake LDAP records for validOnyen()
    //     // and updateFromOnyen() on both the 'default' and 'people' connections.
    //
    //     $response = $this->actingAsAdmin()
    //         ->post(route('admin.team.addPlayer', $team->id), [
    //             'onyen' => 'jdoe',
    //         ]);
    //
    //     $response->assertRedirect();
    //     $player = Player::where('onyen', 'jdoe')->first();
    //     $this->assertNotNull($player);
    //     $this->assertEquals('John', $player->first_name);
    //     $this->assertTrue((bool) $player->student);
    //     $this->assertDatabaseHas('player_team', ['team_id' => $team->id, 'player_id' => $player->id]);
    // }

    // public function test_add_player_with_valid_onyen_reuses_existing_player_record(): void
    // {
    //     $game = Game::factory()->create();
    //     $team = Team::factory()->create(['game_id' => $game->id]);
    //     $existing = Player::factory()->create(['onyen' => 'jsmith']);
    //
    //     // Requires DirectoryFake — validOnyen() must return true for 'jsmith'.
    //
    //     $response = $this->actingAsAdmin()
    //         ->post(route('admin.team.addPlayer', $team->id), ['onyen' => 'jsmith']);
    //
    //     $response->assertRedirect();
    //     $this->assertDatabaseCount('players', 1);
    //     $this->assertDatabaseHas('player_team', ['team_id' => $team->id, 'player_id' => $existing->id]);
    // }

    // public function test_add_player_with_invalid_onyen_redirects_with_warning(): void
    // {
    //     $game = Game::factory()->create();
    //     $team = Team::factory()->create(['game_id' => $game->id]);
    //
    //     // Requires DirectoryFake with no seeded records so validOnyen() returns
    //     // false and the controller redirects back with errors.
    //
    //     $response = $this->actingAsAdmin()
    //         ->post(route('admin.team.addPlayer', $team->id), ['onyen' => 'nobody']);
    //
    //     $response->assertRedirect();
    //     $response->assertSessionHasErrors();
    // }

    // public function test_add_player_with_override_non_student_flag_marks_player_as_student(): void
    // {
    //     $game = Game::factory()->create();
    //     $team = Team::factory()->create(['game_id' => $game->id]);
    //
    //     // Requires DirectoryFake — seeds a non-student LDAP record then
    //     // sends override_non_student=1 to force student=true.
    //
    //     $response = $this->actingAsAdmin()
    //         ->post(route('admin.team.addPlayer', $team->id), [
    //             'onyen'                => 'nonstudent',
    //             'override_non_student' => '1',
    //         ]);
    //
    //     $response->assertRedirect();
    //     $player = Player::where('onyen', 'nonstudent')->first();
    //     $this->assertNotNull($player);
    //     $this->assertTrue((bool) $player->student);
    // }

    // -------------------------------------------------------------------------
    // addPlayer — manual (non-LDAP) path
    // -------------------------------------------------------------------------

    public function test_add_player_manually_creates_player_with_password(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.team.addPlayer', $team->id), [
                'email'               => 'manual@example.com',
                'password'            => 'testpassword',
                'first_name'          => 'John',
                'last_name'           => 'Doe',
                'class_code'          => 'UGRD',
                'academic_group_code' => 'CAS',
            ]);

        $response->assertRedirect();

        $player = Player::where('email', 'manual@example.com')->first();
        $this->assertNotNull($player);
        $this->assertTrue((bool) $player->manual);
        $this->assertTrue(Hash::check('testpassword', $player->password));
        $this->assertEquals('manual@example.com', $player->onyen);

        $this->assertDatabaseHas('player_team', [
            'team_id'   => $team->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_add_player_manually_requires_password(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.team.addPlayer', $team->id), [
                'email' => 'test@example.com',
                // password intentionally omitted
                'first_name'          => 'John',
                'last_name'           => 'Doe',
                'class_code'          => 'UGRD',
                'academic_group_code' => 'CAS',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_add_player_manually_requires_all_fields(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.team.addPlayer', $team->id), [
                'email' => 'test@example.com',
            ]);

        $response->assertSessionHasErrors(['password', 'first_name', 'last_name', 'class_code', 'academic_group_code']);
    }

    public function test_add_player_manually_sets_manual_flag(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $this->actingAsAdmin()
            ->post(route('admin.team.addPlayer', $team->id), [
                'email'               => 'flagtest@example.com',
                'password'            => 'password123',
                'first_name'          => 'Jane',
                'last_name'           => 'Smith',
                'class_code'          => 'GRAD',
                'academic_group_code' => 'CAS',
            ]);

        $player = Player::where('email', 'flagtest@example.com')->first();
        $this->assertNotNull($player);
        $this->assertTrue((bool) $player->manual);
    }

    public function test_add_player_manually_uses_email_as_onyen(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $this->actingAsAdmin()
            ->post(route('admin.team.addPlayer', $team->id), [
                'email'               => 'onyentest@example.com',
                'password'            => 'password123',
                'first_name'          => 'Alice',
                'last_name'           => 'Jones',
                'class_code'          => 'UGRD',
                'academic_group_code' => 'CAS',
            ]);

        $this->assertDatabaseHas('players', [
            'email' => 'onyentest@example.com',
            'onyen' => 'onyentest@example.com',
        ]);
    }

    public function test_add_player_manually_hashes_password(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $this->actingAsAdmin()
            ->post(route('admin.team.addPlayer', $team->id), [
                'email'               => 'hashtest@example.com',
                'password'            => 'plaintextpassword',
                'first_name'          => 'Bob',
                'last_name'           => 'Brown',
                'class_code'          => 'UGRD',
                'academic_group_code' => 'CAS',
            ]);

        $player = Player::where('email', 'hashtest@example.com')->first();
        $this->assertNotEquals('plaintextpassword', $player->password);
        $this->assertTrue(Hash::check('plaintextpassword', $player->password));
    }

    // -------------------------------------------------------------------------
    // removePlayer
    // -------------------------------------------------------------------------

    public function test_remove_player_detaches_player_from_team(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);
        $player = Player::factory()->create();
        $team->players()->attach($player->id);

        // Route is DELETE, not POST
        $response = $this->actingAsAdmin()
            ->delete(route('admin.team.removePlayer', ['id' => $team->id, 'playerId' => $player->id]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('player_team', [
            'team_id'   => $team->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_remove_player_returns_404_for_nonexistent_team(): void
    {
        $player = Player::factory()->create();

        $response = $this->actingAsAdmin()
            ->delete(route('admin.team.removePlayer', ['id' => 999999, 'playerId' => $player->id]));

        $response->assertStatus(404);
    }

    public function test_remove_player_leaves_other_players_attached(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);
        $player1 = Player::factory()->create();
        $player2 = Player::factory()->create();
        $team->players()->attach([$player1->id, $player2->id]);

        $this->actingAsAdmin()
            ->delete(route('admin.team.removePlayer', ['id' => $team->id, 'playerId' => $player1->id]));

        $this->assertDatabaseMissing('player_team', ['team_id' => $team->id, 'player_id' => $player1->id]);
        $this->assertDatabaseHas('player_team', ['team_id' => $team->id, 'player_id' => $player2->id]);
    }
}

