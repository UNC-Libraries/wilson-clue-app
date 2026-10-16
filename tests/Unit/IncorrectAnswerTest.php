<?php

namespace Tests\Unit;

use App\Game;
use App\IncorrectAnswer;
use App\Question;
use App\Quest;
use App\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncorrectAnswerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable / Casts
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $incorrectAnswer = new IncorrectAnswer();

        $this->assertEquals(['team_id', 'question_id', 'answer'], $incorrectAnswer->getFillable());
    }

    public function test_it_can_be_mass_assigned_fillable_fields(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->create();

        $incorrectAnswer = new IncorrectAnswer([
            'team_id' => $team->id,
            'question_id' => $question->id,
            'answer' => 'Wrong answer',
            'judged' => 1,
        ]);

        $this->assertEquals($team->id, $incorrectAnswer->team_id);
        $this->assertEquals($question->id, $incorrectAnswer->question_id);
        $this->assertEquals('Wrong answer', $incorrectAnswer->answer);
        $this->assertNull($incorrectAnswer->judged);
    }

    public function test_it_casts_judged_to_boolean(): void
    {
        $incorrectAnswer = IncorrectAnswer::factory()->create(['judged' => 1]);

        $this->assertIsBool($incorrectAnswer->judged);
        $this->assertTrue($incorrectAnswer->judged);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_a_question_belongs_to_relationship(): void
    {
        $incorrectAnswer = new IncorrectAnswer();

        $this->assertInstanceOf(BelongsTo::class, $incorrectAnswer->question());
        $this->assertInstanceOf(Question::class, $incorrectAnswer->question()->getRelated());
    }

    public function test_it_has_a_team_belongs_to_relationship(): void
    {
        $incorrectAnswer = new IncorrectAnswer();

        $this->assertInstanceOf(BelongsTo::class, $incorrectAnswer->team());
        $this->assertInstanceOf(Team::class, $incorrectAnswer->team()->getRelated());
    }

    public function test_it_belongs_to_expected_question_and_team(): void
    {
        $question = Question::factory()->create();
        $team = Team::factory()->create();

        $incorrectAnswer = IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
        ]);

        $this->assertEquals($question->id, $incorrectAnswer->question->id);
        $this->assertEquals($team->id, $incorrectAnswer->team->id);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function judged_scope_returns_only_judged_records(): void
    {
        $judged = IncorrectAnswer::factory()->create(['judged' => true]);
        IncorrectAnswer::factory()->create(['judged' => false]);

        $results = IncorrectAnswer::query()->judged()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($judged->id, $results->first()->id);
    }

    public function not_judged_scope_returns_only_not_judged_records(): void
    {
        $notJudged = IncorrectAnswer::factory()->create(['judged' => false]);
        IncorrectAnswer::factory()->create(['judged' => true]);

        $results = IncorrectAnswer::query()->notJudged()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($notJudged->id, $results->first()->id);
    }

    public function of_game_scope_returns_answers_linked_to_questions_on_that_games_quests(): void
    {
        $game = Game::factory()->create();
        $otherGame = Game::factory()->create();

        $questInGame = Quest::factory()->create(['game_id' => $game->id]);
        $questInOtherGame = Quest::factory()->create(['game_id' => $otherGame->id]);

        $questionInGame = Question::factory()->create();
        $questionInOtherGame = Question::factory()->create();

        $questInGame->questions()->attach($questionInGame->id, ['order' => 1]);
        $questInOtherGame->questions()->attach($questionInOtherGame->id, ['order' => 1]);

        $included = IncorrectAnswer::factory()->create(['question_id' => $questionInGame->id]);
        IncorrectAnswer::factory()->create(['question_id' => $questionInOtherGame->id]);

        $results = IncorrectAnswer::query()->ofGame($game->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($included->id, $results->first()->id);
    }

    public function of_game_scope_returns_empty_collection_for_nonexistent_game(): void
    {
        $question = Question::factory()->create();
        IncorrectAnswer::factory()->create(['question_id' => $question->id]);

        $results = IncorrectAnswer::query()->ofGame(999999)->get();

        $this->assertCount(0, $results);
    }
}

