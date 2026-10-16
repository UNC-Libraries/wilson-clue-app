<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\GhostDna;
use App\MinigameImage;
use App\Player;
use App\Quest;
use App\Question;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): AdminControllerTest
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_displays_admin_dashboard(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('homepageAlert');
    }

    public function test_index_loads_homepage_alert_from_globals(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Welcome to the game!',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin'));

        $response->assertStatus(200);
        $response->assertViewHas('homepageAlert', 'Welcome to the game!');
    }

    public function test_index_returns_empty_homepage_alert_when_not_found(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin'));

        $response->assertStatus(200);
        $response->assertViewHas('homepageAlert', '');
    }

    public function test_index_handles_null_message_in_globals(): void
    {
        // globals.message is NOT NULL in the migration; use an empty string to
        // represent a row that exists but carries no meaningful message.
        DB::table('globals')->insert([
            'key'     => 'homepage',
            'message' => '',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin'));

        $response->assertStatus(200);
        $response->assertViewHas('homepageAlert');
    }

    // -------------------------------------------------------------------------
    // trash
    // -------------------------------------------------------------------------

    public function test_trash_displays_soft_deleted_games(): void
    {
        $activeGame = Game::factory()->create(['name' => 'Active Game']);
        $deletedGame = Game::factory()->create(['name' => 'Deleted Game']);
        $deletedGame->delete();

        $response = $this->actingAsAdmin()
            ->get(route('admin.trash'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.trash');
        $response->assertViewHas('games', function ($games) use ($activeGame, $deletedGame) {
            return !$games->pluck('id')->contains($activeGame->id)
                && $games->pluck('id')->contains($deletedGame->id);
        });
    }

    public function test_trash_returns_empty_collection_when_no_trashed_games(): void
    {
        Game::factory()->create(['name' => 'Active Game']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.trash'));

        $response->assertStatus(200);
        $response->assertViewHas('games', function ($games) {
            return $games->isEmpty();
        });
    }

    public function test_trash_shows_multiple_soft_deleted_games(): void
    {
        $deleted1 = Game::factory()->create(['name' => 'Deleted 1']);
        $deleted2 = Game::factory()->create(['name' => 'Deleted 2']);

        $deleted1->delete();
        $deleted2->delete();

        $response = $this->actingAsAdmin()
            ->get(route('admin.trash'));

        $response->assertStatus(200);
        $response->assertViewHas('games', function ($games) use ($deleted1, $deleted2) {
            return $games->count() === 2
                && $games->pluck('id')->contains($deleted1->id)
                && $games->pluck('id')->contains($deleted2->id);
        });
    }

    // -------------------------------------------------------------------------
    // restore
    // -------------------------------------------------------------------------

    public function test_restore_restores_soft_deleted_game(): void
    {
        $game = Game::factory()->create();
        $game->delete();

        $this->assertSoftDeleted('games', ['id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.restore', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'deleted_at' => null,
        ]);
    }

    public function test_restore_restores_game_teams(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        // Soft-delete the team explicitly so the controller's restore() call
        // can bring it back. Eloquent does not cascade soft-deletes automatically.
        $team->delete();
        $game->delete();

        $this->assertSoftDeleted('teams', ['id' => $team->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.restore', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseHas('teams', [
            'id'         => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_restore_restores_game_quests(): void
    {
        $game = Game::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);

        // Soft-delete the quest explicitly so the controller's restore() call
        // can bring it back. Eloquent does not cascade soft-deletes automatically.
        $quest->delete();
        $game->delete();

        $this->assertSoftDeleted('quests', ['id' => $quest->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.restore', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseHas('quests', [
            'id'         => $quest->id,
            'deleted_at' => null,
        ]);
    }

    public function test_restore_returns_404_for_nonexistent_game(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.restore', 999999));

        $response->assertStatus(404);
    }

    public function test_restore_redirects_to_admin_dashboard(): void
    {
        $game = Game::factory()->create();
        $game->delete();

        $response = $this->actingAsAdmin()
            ->post(route('admin.restore', $game->id));

        $response->assertRedirect(route('admin'));
    }

    // -------------------------------------------------------------------------
    // delete (force delete)
    // -------------------------------------------------------------------------

    public function test_delete_permanently_deletes_game(): void
    {
        $game = Game::factory()->create();
        $game->delete();

        $response = $this->actingAsAdmin()
            ->delete(route('admin.delete', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_delete_detaches_quest_relationships_before_deletion(): void
    {
        $game = Game::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $question = Question::factory()->create();
        $minigameImage = MinigameImage::factory()->create();

        $quest->completedBy()->attach($team->id);
        $quest->questions()->attach($question->id, ['order' => 1]);
        $quest->minigameImages()->attach($minigameImage->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.delete', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('quest_team', [
            'quest_id' => $quest->id,
            'team_id' => $team->id,
        ]);
        $this->assertDatabaseMissing('quest_question', [
            'quest_id' => $quest->id,
            'question_id' => $question->id,
        ]);
        $this->assertDatabaseMissing('minigame_image_quest', [
            'quest_id' => $quest->id,
            'minigame_image_id' => $minigameImage->id,
        ]);
    }

    public function test_delete_detaches_team_relationships_before_deletion(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $player = Player::factory()->create();
        $question = Question::factory()->create();
        $dna = GhostDna::factory()->create();

        $team->players()->attach($player->id);
        $team->correctQuestions()->attach($question->id);
        $team->foundDna()->attach($dna->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.delete', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('player_team', [
            'team_id' => $team->id,
            'player_id' => $player->id,
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

    public function test_delete_deletes_team_incorrect_answers(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);
        $question = Question::factory()->create();

        $incorrectAnswer = \App\IncorrectAnswer::factory()->create([
            'team_id' => $team->id,
            'question_id' => $question->id,
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.delete', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('incorrect_answers', [
            'id' => $incorrectAnswer->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_delete_force_deletes_teams_and_quests(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);
        $quest = Quest::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.delete', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
        $this->assertDatabaseMissing('quests', ['id' => $quest->id]);
    }

    public function test_delete_handles_game_with_multiple_teams_and_quests(): void
    {
        $game = Game::factory()->create();
        Team::factory()->count(2)->create(['game_id' => $game->id]);
        Quest::factory()->count(2)->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.delete', $game->id));

        $response->assertRedirect(route('admin'));
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    // -------------------------------------------------------------------------
    // siteMessages
    // -------------------------------------------------------------------------

    public function test_site_messages_displays_configured_messages(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Homepage alert',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.siteMessages'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.siteMessages');
        $response->assertViewHas('messages');
    }

    public function test_site_messages_creates_missing_global_entries(): void
    {
        // Ensure no globals exist
        DB::table('globals')->truncate();

        $response = $this->actingAsAdmin()
            ->get(route('admin.siteMessages'));

        $response->assertStatus(200);

        // Check that entries were created for configured keys
        $configKeys = array_keys(config('site_messages'));

        foreach ($configKeys as $key) {
            $this->assertDatabaseHas('globals', [
                'key' => $key,
                'message' => '',
            ]);
        }
    }

    public function test_site_messages_loads_existing_messages_from_database(): void
    {
        DB::table('globals')->insert([
            ['key' => 'homepage', 'message' => 'Homepage message'],
            ['key' => 'special_notice', 'message' => 'Special notice text'],
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.siteMessages'));

        $response->assertStatus(200);
        $response->assertViewHas('messages', function ($messages) {
            $homepage = $messages->get('homepage');
            $specialNotice = $messages->get('special_notice');

            return is_array($homepage)
                && ($homepage['message'] ?? null) === 'Homepage message'
                && is_array($specialNotice)
                && ($specialNotice['message'] ?? null) === 'Special notice text';
        });
    }

    public function test_site_messages_handles_empty_config(): void
    {
        config(['site_messages' => []]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.siteMessages'));

        $response->assertStatus(200);
        $response->assertViewHas('messages', function ($messages) {
            return $messages->isEmpty();
        });
    }

    // -------------------------------------------------------------------------
    // updateSiteMessage
    // -------------------------------------------------------------------------

    public function test_update_site_message_updates_existing_global_message(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.update', 'homepage'), [
                'homepage' => 'Updated message',
            ]);

        $response->assertRedirect(route('admin.siteMessages'));

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'Updated message',
        ]);
    }

    public function test_update_site_message_handles_empty_message(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.update', 'homepage'), [
                'homepage' => '',
            ]);

        $response->assertRedirect(route('admin.siteMessages'));

        // Controller updates only truthy values, so empty string is ignored.
        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'Original message',
        ]);
    }

    public function test_update_site_message_does_not_update_when_key_missing_in_request(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.update', 'homepage'), []);

        $response->assertRedirect(route('admin.siteMessages'));

        // Should not update because key not in request
        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'Original message',
        ]);
    }

    public function test_update_site_message_handles_null_value(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.update', 'homepage'), [
                'homepage' => null,
            ]);

        $response->assertRedirect(route('admin.siteMessages'));

        // Null is falsy, so it won't update
        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'Original message',
        ]);
    }

    public function test_update_site_message_handles_different_message_keys(): void
    {
        DB::table('globals')->insert([
            ['key' => 'special_notice', 'message' => 'Original special'],
            ['key' => 'registration_closed', 'message' => 'Original closed'],
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.update', 'special_notice'), [
                'special_notice' => 'Updated special notice',
            ]);

        $response->assertRedirect(route('admin.siteMessages'));

        $this->assertDatabaseHas('globals', [
            'key' => 'special_notice',
            'message' => 'Updated special notice',
        ]);

        // Other messages should be unchanged
        $this->assertDatabaseHas('globals', [
            'key' => 'registration_closed',
            'message' => 'Original closed',
        ]);
    }

    public function test_update_site_message_redirects_to_site_messages(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.update', 'homepage'), [
                'homepage' => 'Updated',
            ]);

        $response->assertRedirect(route('admin.siteMessages'));
    }
}

