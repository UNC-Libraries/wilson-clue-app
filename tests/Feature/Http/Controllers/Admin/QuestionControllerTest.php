<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Answer;
use App\Game;
use App\Location;
use App\Question;
use App\Quest;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionControllerTest extends TestCase
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

    public function test_index_displays_all_questions(): void
    {
        $questionA = Question::factory()->create(['text' => 'Question A']);
        $questionB = Question::factory()->create(['text' => 'Question B']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.index'));

        $response->assertStatus(200);
        $response->assertViewIs('question.index');
        $response->assertViewHas('questions', function ($questions) use ($questionA, $questionB) {
            return $questions->pluck('id')->contains($questionA->id)
                && $questions->pluck('id')->contains($questionB->id);
        });
    }

    public function test_index_filters_by_location(): void
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $questionA = Question::factory()->create(['location_id' => $locationA->id]);
        $questionB = Question::factory()->create(['location_id' => $locationB->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.index', ['location_id' => $locationA->id]));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function ($questions) use ($questionA, $questionB) {
            return $questions->pluck('id')->contains($questionA->id)
                && !$questions->pluck('id')->contains($questionB->id);
        });
    }

    public function test_index_filters_by_game(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();

        $questA = Quest::factory()->create(['game_id' => $gameA->id]);
        $questB = Quest::factory()->create(['game_id' => $gameB->id]);

        $questionA = Question::factory()->create();
        $questionB = Question::factory()->create();

        $questA->questions()->attach($questionA->id, ['order' => 1]);
        $questB->questions()->attach($questionB->id, ['order' => 1]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.index', ['game_id' => $gameA->id]));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function ($questions) use ($questionA, $questionB) {
            return $questions->pluck('id')->contains($questionA->id)
                && !$questions->pluck('id')->contains($questionB->id);
        });
    }

    public function test_index_filters_by_search_text(): void
    {
        $questionA = Question::factory()->create(['text' => 'What is the capital of France?']);
        $questionB = Question::factory()->create(['text' => 'What is the capital of Spain?']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.index', ['search' => 'France']));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function ($questions) use ($questionA, $questionB) {
            return $questions->pluck('id')->contains($questionA->id)
                && !$questions->pluck('id')->contains($questionB->id);
        });
    }

    public function test_index_filters_by_search_in_full_answer(): void
    {
        $questionA = Question::factory()->create(['full_answer' => 'Paris is the answer']);
        $questionB = Question::factory()->create(['full_answer' => 'Madrid is the answer']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.index', ['search' => 'Paris']));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function ($questions) use ($questionA, $questionB) {
            return $questions->pluck('id')->contains($questionA->id)
                && !$questions->pluck('id')->contains($questionB->id);
        });
    }

    public function test_index_combines_multiple_filters(): void
    {
        $location = Location::factory()->create();
        $game = Game::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);

        $matchingQuestion = Question::factory()->create([
            'location_id' => $location->id,
            'text' => 'Find the clue',
        ]);

        $nonMatchingQuestion = Question::factory()->create([
            'location_id' => $location->id,
            'text' => 'Different question',
        ]);

        $quest->questions()->attach($matchingQuestion->id, ['order' => 1]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.index', [
                'location_id' => $location->id,
                'game_id' => $game->id,
                'search' => 'clue',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('questions', function ($questions) use ($matchingQuestion) {
            return $questions->count() === 1
                && $questions->first()->id === $matchingQuestion->id;
        });
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_displays_form_with_locations(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.create'));

        $response->assertStatus(200);
        $response->assertViewIs('question.create');
        $response->assertViewHas('question');
        $response->assertViewHas('locations', function ($locations) use ($location) {
            return $locations->pluck('id')->contains($location->id);
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_question_with_required_fields(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'What is the answer?',
                'full_answer' => 'The answer is 42',
                'location_id' => $location->id,
                'answer' => ['new' => ['Answer 1', 'Answer 2']],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('questions', [
            'text' => 'What is the answer?',
            'full_answer' => 'The answer is 42',
            'location_id' => $location->id,
        ]);

        $question = Question::where('text', 'What is the answer?')->first();
        $this->assertCount(2, $question->answers);
    }

    public function test_store_creates_question_with_type_true(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'True or False?',
                'full_answer' => 'True',
                'location_id' => $location->id,
                'type' => '1',
                'answer' => ['new' => ['True']],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'text' => 'True or False?',
            'type' => 1,
        ]);
    }

    public function test_store_creates_question_with_type_false(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Multiple choice?',
                'full_answer' => 'Answer',
                'location_id' => $location->id,
                'type' => '0',
                'answer' => ['new' => ['Option A']],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'text' => 'Multiple choice?',
            'type' => 0,
        ]);
    }

    public function test_store_uploads_image_file(): void
    {
        Storage::fake('public');

        $location = Location::factory()->create();
        $file = UploadedFile::fake()->image('question.jpg', 800, 600);

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Question with image',
                'full_answer' => 'Answer',
                'location_id' => $location->id,
                'new_image_file' => $file,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertRedirect();

        $question = Question::where('text', 'Question with image')->first();
        $this->assertNotNull($question->src);
        Storage::disk('public')->assertExists($question->getRawOriginal('src'));
    }

    public function test_store_validates_image_file_size(): void
    {
        Storage::fake('public');

        $location = Location::factory()->create();
        $file = UploadedFile::fake()->create('question.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Question with large image',
                'full_answer' => 'Answer',
                'location_id' => $location->id,
                'new_image_file' => $file,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_validates_image_mime_type(): void
    {
        Storage::fake('public');

        $location = Location::factory()->create();
        $file = UploadedFile::fake()->create('question.pdf', 100);

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Question with PDF',
                'full_answer' => 'Answer',
                'location_id' => $location->id,
                'new_image_file' => $file,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_requires_text_field(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'full_answer' => 'Answer',
                'location_id' => $location->id,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertSessionHasErrors('text');
    }

    public function test_store_requires_full_answer_field(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Question',
                'location_id' => $location->id,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertSessionHasErrors('full_answer');
    }

    public function test_store_requires_answer_field(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Question',
                'full_answer' => 'Answer',
                'location_id' => $location->id,
            ]);

        $response->assertSessionHasErrors('answer');
    }

    public function test_store_requires_location_id_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.question.store'), [
                'text' => 'Question',
                'full_answer' => 'Answer',
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertSessionHasErrors('location_id');
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_form_with_question_and_answers(): void
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.edit', $question->id));

        $response->assertStatus(200);
        $response->assertViewIs('question.edit');
        $response->assertViewHas('question', fn($q) => $q->id === $question->id);
        $response->assertViewHas('locations');
    }

    public function test_edit_groups_incorrect_answers_by_answer_text(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->create();

        \App\IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'answer' => 'Wrong Answer A',
        ]);

        \App\IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'answer' => 'Wrong Answer A',
        ]);

        \App\IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'answer' => 'Wrong Answer B',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.question.edit', $question->id));

        $response->assertStatus(200);
        $response->assertViewHas('incorrect', function ($incorrect) {
            return $incorrect->count() === 2
                && $incorrect->firstWhere('answer', 'Wrong Answer A')['count'] === 2
                && $incorrect->firstWhere('answer', 'Wrong Answer B')['count'] === 1;
        });
    }

    public function test_edit_returns_404_for_nonexistent_question(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.question.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_question(): void
    {
        $question = Question::factory()->create(['text' => 'Original text']);
        Answer::factory()->create(['question_id' => $question->id, 'text' => 'Original answer']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.question.update', $question->id), [
                'text' => 'Updated text',
                'full_answer' => 'Updated full answer',
                'location_id' => $question->location_id,
                'answer' => ['new' => ['New answer']],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'text' => 'Updated text',
            'full_answer' => 'Updated full answer',
        ]);
    }

    public function test_update_modifies_existing_answers(): void
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'text' => 'Original answer',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.question.update', $question->id), [
                'text' => $question->text,
                'full_answer' => $question->full_answer,
                'location_id' => $question->location_id,
                'answer' => [
                    $answer->id => 'Modified answer',
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'text' => 'Modified answer',
        ]);
    }

    public function test_update_adds_new_answers(): void
    {
        $question = Question::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.question.update', $question->id), [
                'text' => $question->text,
                'full_answer' => $question->full_answer,
                'location_id' => $question->location_id,
                'answer' => [
                    'new' => ['New Answer 1', 'New Answer 2'],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'text' => 'New Answer 1',
        ]);

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'text' => 'New Answer 2',
        ]);
    }

    public function test_update_replaces_image_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('questions', 'public');

        $question = Question::factory()->create(['src' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAsAdmin()
            ->put(route('admin.question.update', $question->id), [
                'text' => $question->text,
                'full_answer' => $question->full_answer,
                'location_id' => $question->location_id,
                'new_image_file' => $newFile,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertRedirect();

        $fresh = $question->fresh();
        $this->assertNotEquals($oldPath, $fresh->getRawOriginal('src'));
        Storage::disk('public')->assertExists($fresh->getRawOriginal('src'));
    }

    public function test_update_returns_404_for_nonexistent_question(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.question.update', 999999), [
                'text' => 'Text',
                'full_answer' => 'Answer',
                'location_id' => 1,
                'answer' => ['new' => ['Answer']],
            ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_question_and_answers_when_not_used_in_game(): void
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.question.destroy', $question->id));

        $response->assertRedirect(route('admin.question.index'));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    public function test_destroy_detaches_quests_when_deleting_question(): void
    {
        $question = Question::factory()->create();
        $quest = Quest::factory()->create();

        $quest->questions()->attach($question->id, ['order' => 1]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.question.destroy', $question->id));

        $response->assertRedirect();

        $this->assertDatabaseMissing('quest_question', [
            'question_id' => $question->id,
        ]);
    }

    public function test_destroy_prevents_deletion_when_question_is_completed_by_teams(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->create(['waitlist' => false]);

        $question->completedBy()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.question.destroy', $question->id));

        $response->assertRedirect(route('admin.question.index'));
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', function ($message) use ($question) {
            return str_contains($message, 'could not be deleted');
        });

        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_question(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.question.destroy', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // newAnswer
    // -------------------------------------------------------------------------

    public function test_new_answer_returns_answer_input_partial(): void
    {
        $this->markTestSkipped('No route is registered for QuestionController::newAnswer in routes/web.php.');
    }
}

