<?php

namespace Tests\Unit;

use App\Answer;
use App\IncorrectAnswer;
use App\Location;
use App\Quest;
use App\Question;
use App\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable / Hidden / Casts
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $question = new Question();

        $this->assertEquals([
            'text',
            'type',
            'full_answer',
            'src',
            'location_id',
        ], $question->getFillable());
    }

    public function test_it_hides_full_answer_and_answers_from_serialization(): void
    {
        $question = Question::factory()->create([
            'full_answer' => 'Secret Answer',
        ]);

        $array = $question->toArray();

        $this->assertArrayNotHasKey('full_answer', $array);
        $this->assertArrayNotHasKey('answers', $array);
    }

    public function test_it_casts_type_to_boolean(): void
    {
        $question = Question::factory()->create(['type' => 1]);

        $this->assertIsBool($question->type);
        $this->assertTrue($question->type);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_expected_relationship_types(): void
    {
        $question = new Question();

        $this->assertInstanceOf(HasMany::class, $question->answers());
        $this->assertInstanceOf(BelongsToMany::class, $question->quests());
        $this->assertInstanceOf(HasMany::class, $question->incorrectAnswers());
        $this->assertInstanceOf(BelongsTo::class, $question->location());
        $this->assertInstanceOf(BelongsToMany::class, $question->completedBy());
    }

    public function test_it_uses_expected_related_models_for_relationships(): void
    {
        $question = new Question();

        $this->assertInstanceOf(Answer::class, $question->answers()->getRelated());
        $this->assertInstanceOf(Quest::class, $question->quests()->getRelated());
        $this->assertInstanceOf(IncorrectAnswer::class, $question->incorrectAnswers()->getRelated());
        $this->assertInstanceOf(Location::class, $question->location()->getRelated());
        $this->assertInstanceOf(Team::class, $question->completedBy()->getRelated());
    }

    public function test_completed_by_relationship_uses_timestamps(): void
    {
        $question = new Question();
        $relation = $question->completedBy();

        $this->assertTrue($relation->hasPivotColumn('created_at'));
        $this->assertTrue($relation->hasPivotColumn('updated_at'));
    }

    public function test_it_loads_related_entities_correctly(): void
    {
        $location = Location::factory()->create();
        $question = Question::factory()->create(['location_id' => $location->id]);

        $answer = Answer::factory()->create(['question_id' => $question->id]);
        $incorrectAnswer = IncorrectAnswer::factory()->create(['question_id' => $question->id]);
        $quest = Quest::factory()->create();
        $team = Team::factory()->create(['waitlist' => false]);

        $question->quests()->attach($quest->id, ['order' => 1]);
        $question->completedBy()->attach($team->id);

        $fresh = $question->fresh();

        $this->assertEquals($location->id, $fresh->location->id);
        $this->assertCount(1, $fresh->answers);
        $this->assertEquals($answer->id, $fresh->answers->first()->id);
        $this->assertCount(1, $fresh->incorrectAnswers);
        $this->assertEquals($incorrectAnswer->id, $fresh->incorrectAnswers->first()->id);
        $this->assertCount(1, $fresh->quests);
        $this->assertEquals($quest->id, $fresh->quests->first()->id);
        $this->assertCount(1, $fresh->completedBy);
        $this->assertEquals($team->id, $fresh->completedBy->first()->id);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function of_quest_scope_returns_only_questions_for_the_given_quest(): void
    {
        $questA = Quest::factory()->create();
        $questB = Quest::factory()->create();

        $questionA = Question::factory()->create();
        $questionB = Question::factory()->create();

        $questA->questions()->attach($questionA->id, ['order' => 1]);
        $questB->questions()->attach($questionB->id, ['order' => 1]);

        $results = Question::query()->ofQuest($questA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($questionA->id, $results->first()->id);
    }

    public function of_quest_scope_returns_empty_collection_for_nonexistent_quest(): void
    {
        Question::factory()->create();

        $results = Question::query()->ofQuest(999999)->get();

        $this->assertCount(0, $results);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function test_src_accessor_prepends_public_uploads_path(): void
    {
        $question = Question::factory()->make(['src' => 'questions/image.jpg']);

        $this->assertEquals(env('PUBLIC_UPLOADS_PATH').'/questions/image.jpg', $question->src);
    }

    public function test_src_accessor_handles_null_value(): void
    {
        $question = new Question();

        $this->assertEquals(env('PUBLIC_UPLOADS_PATH').'/', $question->src);
    }

    public function test_not_judged_answers_excludes_judged_answers(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->create();

        $judgedAnswer = IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => true,
        ]);

        $notJudgedAnswer = IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => false,
        ]);

        $results = $question->fresh()->not_judged_answers;

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(1, $results);
        $this->assertEquals($notJudgedAnswer->id, $results->first()->id);
    }

    public function test_not_judged_answers_excludes_answers_from_teams_that_completed_question(): void
    {
        $question = Question::factory()->create();
        $completedTeam = Team::factory()->create(['waitlist' => false]);
        $otherTeam = Team::factory()->create(['waitlist' => false]);

        $question->completedBy()->attach($completedTeam->id);

        IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $completedTeam->id,
            'judged' => false,
        ]);

        $notJudgedAnswer = IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $otherTeam->id,
            'judged' => false,
        ]);

        $results = $question->fresh()->not_judged_answers;

        $this->assertCount(1, $results);
        $this->assertEquals($notJudgedAnswer->id, $results->first()->id);
    }

    public function test_not_judged_answers_excludes_answers_with_null_team(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->withGame()->create();
        $deletedTeam = Team::factory()->withGame()->create();

        IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $deletedTeam->id,
            'judged' => false,
        ]);
        $deletedTeam->delete();

        $validAnswer = IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => false,
        ]);

        $results = $question->fresh()->not_judged_answers;

        $this->assertCount(1, $results);
        $this->assertEquals($validAnswer->id, $results->first()->id);
    }

    public function test_needs_judgement_returns_true_when_not_judged_answers_exist(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->create();

        IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => false,
        ]);

        $this->assertTrue($question->fresh()->needs_judgement);
    }

    public function test_needs_judgement_returns_false_when_no_not_judged_answers_exist(): void
    {
        $question = Question::factory()->create();

        $this->assertFalse($question->needs_judgement);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function delete_image_deletes_file_when_it_exists(): void
    {
        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('delete')->once();

        $question = Question::factory()->make(['src' => 'questions/image.jpg']);
        $question->deleteImage();
    }

    public function delete_image_does_not_delete_when_file_is_missing(): void
    {
        File::shouldReceive('exists')->once()->andReturn(false);
        File::shouldReceive('delete')->never();

        $question = Question::factory()->make(['src' => 'questions/image.jpg']);
        $question->deleteImage();
    }

    public function delete_image_checks_the_expected_upload_path(): void
    {
        $uploadPath = config('filesystems.disks.public.root');

        File::shouldReceive('exists')
            ->once()
            ->with("$uploadPath/questions/image.jpg")
            ->andReturn(true);
        File::shouldReceive('delete')->once();

        $question = Question::factory()->make(['src' => 'questions/image.jpg']);
        $question->deleteImage();
    }
}

