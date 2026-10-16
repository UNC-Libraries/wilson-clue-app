<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\Location;
use App\MinigameImage;
use App\Quest;
use App\Question;
use App\Suspect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestControllerTest extends TestCase
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

    public function test_edit_displays_quest_with_related_data(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $suspect = Suspect::factory()->create();

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'suspect_id' => $suspect->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, $quest->id]));

        $response->assertStatus(200);
        $response->assertViewIs('quest.edit');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
        $response->assertViewHas('quest', fn($q) => $q->id === $quest->id);
        $response->assertViewHas('suspects');
        $response->assertViewHas('locations');
        $response->assertViewHas('games');
        $response->assertViewHas('questions');
        $response->assertViewHas('minigameImages');
    }

    public function test_edit_loads_quest_with_eager_loaded_relationships(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $suspect = Suspect::factory()->create();

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'suspect_id' => $suspect->id,
        ]);

        $question = Question::factory()->create(['location_id' => $location->id]);
        $quest->questions()->attach($question->id, ['order' => 1]);

        $minigameImage = MinigameImage::factory()->create();
        $quest->minigameImages()->attach($minigameImage->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, $quest->id]));

        $response->assertStatus(200);
        $response->assertViewHas('quest', function($q) use ($location, $suspect, $question, $minigameImage) {
            return $q->location->id === $location->id
                && $q->suspect->id === $suspect->id
                && $q->questions->pluck('id')->contains($question->id)
                && $q->minigameImages->pluck('id')->contains($minigameImage->id);
        });
    }

    public function test_edit_provides_available_questions_for_quest_location(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
        ]);

        $attachedQuestion = Question::factory()->create(['location_id' => $location->id]);
        $quest->questions()->attach($attachedQuestion->id, ['order' => 1]);

        $availableQuestion = Question::factory()->create(['location_id' => $location->id]);
        $otherLocationQuestion = Question::factory()->create(['location_id' => $otherLocation->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, $quest->id]));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function($questions) use ($availableQuestion, $attachedQuestion, $otherLocationQuestion) {
            return $questions->pluck('id')->contains($availableQuestion->id)
                && !$questions->pluck('id')->contains($attachedQuestion->id)
                && !$questions->pluck('id')->contains($otherLocationQuestion->id);
        });
    }

    public function test_edit_orders_available_questions_by_created_at_desc(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
        ]);

        $olderQuestion = Question::factory()->create([
            'location_id' => $location->id,
            'created_at' => now()->subDays(2),
        ]);

        $newerQuestion = Question::factory()->create([
            'location_id' => $location->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, $quest->id]));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function($questions) use ($newerQuestion, $olderQuestion) {
            return $questions->first()->id === $newerQuestion->id
                && $questions->last()->id === $olderQuestion->id;
        });
    }

    public function test_edit_provides_available_minigame_images_excluding_attached(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
        ]);

        $attachedImage = MinigameImage::factory()->create();
        $quest->minigameImages()->attach($attachedImage->id);

        $availableImage = MinigameImage::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, $quest->id]));

        $response->assertStatus(200);
        $response->assertViewHas('minigameImages', function($images) use ($availableImage, $attachedImage) {
            return $images->pluck('id')->contains($availableImage->id)
                && !$images->pluck('id')->contains($attachedImage->id);
        });
    }

    public function test_edit_handles_quest_without_minigame_images(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
        ]);

        $availableImage = MinigameImage::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, $quest->id]));

        $response->assertStatus(200);
        $response->assertViewHas('minigameImages', function($images) use ($availableImage) {
            return $images->pluck('id')->contains($availableImage->id);
        });
    }

    public function test_edit_returns_404_for_nonexistent_game(): void
    {
        $location = Location::factory()->create();
        $quest = Quest::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [999999, $quest->id]));

        $response->assertStatus(404);
    }

    public function test_edit_returns_404_for_nonexistent_quest(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.quest.edit', [$game->id, 999999]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_quest_attributes(): void
    {
        $game = Game::factory()->create();
        $oldLocation = Location::factory()->create();
        $newLocation = Location::factory()->create();
        $oldSuspect = Suspect::factory()->create();
        $newSuspect = Suspect::factory()->create();

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $oldLocation->id,
            'suspect_id' => $oldSuspect->id,
            'type' => 'question',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $newLocation->id,
                'suspect_id' => $newSuspect->id,
                'type' => 'minigame',
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('quests', [
            'id' => $quest->id,
            'location_id' => $newLocation->id,
            'suspect_id' => $newSuspect->id,
            'type' => 'minigame',
        ]);
    }

    public function test_update_attaches_questions_for_question_type_quest(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'type' => 'question',
        ]);

        $question1 = Question::factory()->create();
        $question2 = Question::factory()->create();
        $question3 = Question::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => 'question',
                'question_list' => "{$question1->id},{$question2->id},{$question3->id}",
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseHas('quest_question', [
            'quest_id' => $quest->id,
            'question_id' => $question1->id,
            'order' => 0,
        ]);

        $this->assertDatabaseHas('quest_question', [
            'quest_id' => $quest->id,
            'question_id' => $question2->id,
            'order' => 1,
        ]);

        $this->assertDatabaseHas('quest_question', [
            'quest_id' => $quest->id,
            'question_id' => $question3->id,
            'order' => 2,
        ]);
    }

    public function test_update_attaches_minigame_images_for_minigame_type_quest(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'type' => 'minigame',
        ]);

        $image1 = MinigameImage::factory()->create();
        $image2 = MinigameImage::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => 'minigame',
                'question_list' => '',
                'minigame_image_list' => "{$image1->id},{$image2->id}",
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseHas('minigame_image_quest', [
            'quest_id' => $quest->id,
            'minigame_image_id' => $image1->id,
        ]);

        $this->assertDatabaseHas('minigame_image_quest', [
            'quest_id' => $quest->id,
            'minigame_image_id' => $image2->id,
        ]);
    }

    public function test_update_detaches_old_questions_before_attaching_new_ones(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'type' => 'question',
        ]);

        $oldQuestion = Question::factory()->create();
        $quest->questions()->attach($oldQuestion->id, ['order' => 1]);

        $newQuestion = Question::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => 'question',
                'question_list' => "{$newQuestion->id}",
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseMissing('quest_question', [
            'quest_id' => $quest->id,
            'question_id' => $oldQuestion->id,
        ]);

        $this->assertDatabaseHas('quest_question', [
            'quest_id' => $quest->id,
            'question_id' => $newQuestion->id,
        ]);
    }

    public function test_update_detaches_old_minigame_images_before_attaching_new_ones(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'type' => 'minigame',
        ]);

        $oldImage = MinigameImage::factory()->create();
        $quest->minigameImages()->attach($oldImage->id);

        $newImage = MinigameImage::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => 'minigame',
                'question_list' => '',
                'minigame_image_list' => "{$newImage->id}",
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseMissing('minigame_image_quest', [
            'quest_id' => $quest->id,
            'minigame_image_id' => $oldImage->id,
        ]);

        $this->assertDatabaseHas('minigame_image_quest', [
            'quest_id' => $quest->id,
            'minigame_image_id' => $newImage->id,
        ]);
    }

    public function test_update_clears_game_location_solution_when_quest_location_changes(): void
    {
        $oldLocation = Location::factory()->create();
        $newLocation = Location::factory()->create();

        $game = Game::factory()->create([
            'location_id' => $oldLocation->id,
        ]);

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $oldLocation->id,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $newLocation->id,
                'suspect_id' => $quest->suspect_id,
                'type' => $quest->type,
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'location_id' => 0,
        ]);
    }

    public function test_update_does_not_clear_game_location_solution_when_quest_location_unchanged(): void
    {
        $location = Location::factory()->create();

        $game = Game::factory()->create([
            'location_id' => $location->id,
        ]);

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $location->id,
                'suspect_id' => $quest->suspect_id,
                'type' => $quest->type,
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_update_clears_game_suspect_solution_when_quest_suspect_changes(): void
    {
        $oldSuspect = Suspect::factory()->create();
        $newSuspect = Suspect::factory()->create();
        $location = Location::factory()->create();

        $game = Game::factory()->create([
            'suspect_id' => $oldSuspect->id,
        ]);

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'suspect_id' => $oldSuspect->id,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $newSuspect->id,
                'type' => $quest->type,
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'suspect_id' => 0,
        ]);
    }

    public function test_update_does_not_clear_game_suspect_solution_when_quest_suspect_unchanged(): void
    {
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();

        $game = Game::factory()->create([
            'suspect_id' => $suspect->id,
        ]);

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'suspect_id' => $suspect->id,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $suspect->id,
                'type' => $quest->type,
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'suspect_id' => $suspect->id,
        ]);
    }

    public function test_update_handles_empty_question_list(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'type' => 'question',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => 'question',
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseMissing('quest_question', [
            'quest_id' => $quest->id,
        ]);
    }

    public function test_update_handles_empty_minigame_image_list(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'type' => 'minigame',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => 'minigame',
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));

        $this->assertDatabaseMissing('minigame_image_quest', [
            'quest_id' => $quest->id,
        ]);
    }

    public function test_update_redirects_with_success_message_containing_location_name(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create(['name' => 'Library']);

        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, $quest->id]), [
                'location_id' => $location->id,
                'suspect_id' => $quest->suspect_id,
                'type' => $quest->type,
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertRedirect(route('admin.game.edit', $game->id));
        $response->assertSessionHas('alert.message', 'Library updated');
        $response->assertSessionHas('alert.type', 'success');
    }

    public function test_update_returns_404_for_nonexistent_game(): void
    {
        $location = Location::factory()->create();
        $quest = Quest::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [999999, $quest->id]), [
                'location_id' => $quest->location_id,
                'suspect_id' => $quest->suspect_id,
                'type' => $quest->type,
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_returns_404_for_nonexistent_quest(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.quest.update', [$game->id, 999999]), [
                'location_id' => 1,
                'suspect_id' => 1,
                'type' => 'question',
                'question_list' => '',
                'minigame_image_list' => '',
            ]);

        $response->assertStatus(404);
    }
}

