<?php

namespace Tests\Unit;

use App\Game;
use App\IncorrectAnswer;
use App\Location;
use App\MinigameImage;
use App\Quest;
use App\Question;
use App\Suspect;
use App\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable / Appends
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $quest = new Quest();

        $this->assertEquals([
            'type',
            'location_id',
            'suspect_id',
        ], $quest->getFillable());
    }

    public function test_it_appends_types_attribute(): void
    {
        $quest = new Quest();

        $this->assertEquals(['types'], $quest->getAppends());
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_expected_relationship_types(): void
    {
        $quest = new Quest();

        $this->assertInstanceOf(BelongsTo::class, $quest->location());
        $this->assertInstanceOf(BelongsTo::class, $quest->suspect());
        $this->assertInstanceOf(BelongsToMany::class, $quest->questions());
        $this->assertInstanceOf(BelongsToMany::class, $quest->minigameImages());
        $this->assertInstanceOf(BelongsToMany::class, $quest->completedBy());
        $this->assertInstanceOf(BelongsTo::class, $quest->game());
    }

    public function test_it_uses_expected_related_models_for_relationships(): void
    {
        $quest = new Quest();

        $this->assertInstanceOf(Location::class, $quest->location()->getRelated());
        $this->assertInstanceOf(Suspect::class, $quest->suspect()->getRelated());
        $this->assertInstanceOf(Question::class, $quest->questions()->getRelated());
        $this->assertInstanceOf(MinigameImage::class, $quest->minigameImages()->getRelated());
        $this->assertInstanceOf(Team::class, $quest->completedBy()->getRelated());
        $this->assertInstanceOf(Game::class, $quest->game()->getRelated());
    }

    public function test_questions_relationship_includes_order_pivot_column(): void
    {
        $quest = new Quest();

        $this->assertTrue($quest->questions()->hasPivotColumn('order'));
    }

    public function test_completed_by_relationship_uses_timestamps(): void
    {
        $quest = new Quest();
        $relation = $quest->completedBy();

        $this->assertTrue($relation->hasPivotColumn('created_at'));
        $this->assertTrue($relation->hasPivotColumn('updated_at'));
    }

    public function test_it_loads_related_entities_correctly(): void
    {
        $location = Location::factory()->create();
        $suspect = Suspect::factory()->create();
        $game = Game::factory()->create();

        $quest = Quest::factory()->create([
            'location_id' => $location->id,
            'suspect_id' => $suspect->id,
            'game_id' => $game->id,
        ]);

        $this->assertEquals($location->id, $quest->location->id);
        $this->assertEquals($suspect->id, $quest->suspect->id);
        $this->assertEquals($game->id, $quest->game->id);
    }

    public function test_questions_can_be_attached_with_order_pivot(): void
    {
        $quest = Quest::factory()->create();
        $questionA = Question::factory()->create();
        $questionB = Question::factory()->create();

        $quest->questions()->attach($questionA->id, ['order' => 1]);
        $quest->questions()->attach($questionB->id, ['order' => 2]);

        $questions = $quest->fresh()->questions;

        $this->assertCount(2, $questions);
        $this->assertEquals($questionA->id, $questions[0]->id);
        $this->assertEquals($questionB->id, $questions[1]->id);
    }

    public function test_minigame_images_can_be_attached_and_detached(): void
    {
        $quest = Quest::factory()->create();
        $image = MinigameImage::factory()->create();

        $quest->minigameImages()->attach($image->id);
        $this->assertCount(1, $quest->fresh()->minigameImages);

        $quest->minigameImages()->detach($image->id);
        $this->assertCount(0, $quest->fresh()->minigameImages);
    }

    public function test_completed_by_returns_only_registered_teams(): void
    {
        $quest = Quest::factory()->create();
        $registeredTeam = Team::factory()->create(['waitlist' => false]);
        $waitlistedTeam = Team::factory()->create(['waitlist' => true]);

        $quest->completedBy()->attach([$registeredTeam->id, $waitlistedTeam->id]);

        $completedTeams = $quest->fresh()->completedBy;

        $this->assertCount(1, $completedTeams);
        $this->assertEquals($registeredTeam->id, $completedTeams->first()->id);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function minigame_type_scope_returns_only_minigame_quests(): void
    {
        $minigameQuest = Quest::factory()->create(['type' => 'minigame']);
        Quest::factory()->create(['type' => 'question']);

        $results = Quest::query()->minigameType()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($minigameQuest->id, $results->first()->id);
    }

    public function question_type_scope_returns_only_question_quests(): void
    {
        $questionQuest = Quest::factory()->create(['type' => 'question']);
        Quest::factory()->create(['type' => 'minigame']);

        $results = Quest::query()->questionType()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($questionQuest->id, $results->first()->id);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function test_types_accessor_returns_expected_type_mapping(): void
    {
        $quest = Quest::factory()->make();

        $expected = [
            'question' => 'Question',
            'minigame' => 'First Floor Minigame',
        ];

        $this->assertEquals($expected, $quest->types);
    }

    public function test_team_completed_returns_true_when_team_has_completed_quest(): void
    {
        $quest = Quest::factory()->create();
        $team = Team::factory()->create(['waitlist' => false]);

        $quest->completedBy()->attach($team->id);

        $this->assertTrue($quest->fresh()->getTeamCompletedAttribute($team->id));
    }

    public function test_team_completed_returns_false_when_team_has_not_completed_quest(): void
    {
        $quest = Quest::factory()->create();
        $team = Team::factory()->create(['waitlist' => false]);

        $this->assertFalse($quest->getTeamCompletedAttribute($team->id));
    }

    public function test_needs_judgement_returns_true_when_any_question_needs_judgement(): void
    {
        $quest = Quest::factory()->create();
        $question = Question::factory()->create();
        $team = Team::factory()->create(['waitlist' => false]);

        $quest->questions()->attach($question->id, ['order' => 1]);

        IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => false,
        ]);

        $this->assertTrue($quest->fresh()->needs_judgement);
    }

    public function test_needs_judgement_returns_false_when_no_questions_need_judgement(): void
    {
        $quest = Quest::factory()->create();
        $question = Question::factory()->create();

        $quest->questions()->attach($question->id, ['order' => 1]);

        $this->assertFalse($quest->fresh()->needs_judgement);
    }

    // -------------------------------------------------------------------------
    // Soft Deletes
    // -------------------------------------------------------------------------

    public function test_it_soft_deletes(): void
    {
        $quest = Quest::factory()->create();

        $quest->delete();

        $this->assertSoftDeleted('quests', ['id' => $quest->id]);
    }

    public function soft_deleted_quests_are_excluded_from_queries(): void
    {
        $quest = Quest::factory()->create();

        $quest->delete();

        $this->assertNull(Quest::find($quest->id));
    }

    public function soft_deleted_quests_can_be_restored(): void
    {
        $quest = Quest::factory()->create();

        $quest->delete();
        $quest->restore();

        $this->assertNotNull(Quest::find($quest->id));
    }
}

