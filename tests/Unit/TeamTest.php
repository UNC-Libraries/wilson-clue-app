<?php

namespace Tests\Unit;

use App\Evidence;
use App\Game;
use App\GhostDna;
use App\IncorrectAnswer;
use App\Location;
use App\Player;
use App\Question;
use App\Quest;
use App\Suspect;
use App\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable / Casts
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $team = new Team();

        $this->assertEquals(['name', 'dietary', 'bonus_points'], $team->getFillable());
    }

    public function test_it_casts_waitlist_to_boolean_and_score_to_float(): void
    {
        $team = Team::factory()->withGame()->create(['waitlist' => 1, 'score' => 42]);

        $this->assertIsBool($team->waitlist);
        $this->assertTrue($team->waitlist);
        $this->assertIsFloat($team->score);
        $this->assertSame(42.0, $team->score);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_expected_relationship_types(): void
    {
        $team = new Team();

        $this->assertInstanceOf(BelongsTo::class, $team->game());
        $this->assertInstanceOf(BelongsToMany::class, $team->players());
        $this->assertInstanceOf(BelongsToMany::class, $team->checkedInPlayers());
        $this->assertInstanceOf(BelongsTo::class, $team->suspect());
        $this->assertInstanceOf(BelongsTo::class, $team->location());
        $this->assertInstanceOf(BelongsTo::class, $team->evidence());
        $this->assertInstanceOf(BelongsToMany::class, $team->correctQuestions());
        $this->assertInstanceOf(BelongsToMany::class, $team->completedQuests());
        $this->assertInstanceOf(BelongsToMany::class, $team->foundDna());
        $this->assertInstanceOf(HasMany::class, $team->incorrectAnswers());
    }

    public function test_it_uses_expected_related_models_for_relationships(): void
    {
        $team = new Team();

        $this->assertInstanceOf(Game::class, $team->game()->getRelated());
        $this->assertInstanceOf(Player::class, $team->players()->getRelated());
        $this->assertInstanceOf(Suspect::class, $team->suspect()->getRelated());
        $this->assertInstanceOf(Location::class, $team->location()->getRelated());
        $this->assertInstanceOf(Evidence::class, $team->evidence()->getRelated());
        $this->assertInstanceOf(Question::class, $team->correctQuestions()->getRelated());
        $this->assertInstanceOf(Quest::class, $team->completedQuests()->getRelated());
        $this->assertInstanceOf(GhostDna::class, $team->foundDna()->getRelated());
        $this->assertInstanceOf(IncorrectAnswer::class, $team->incorrectAnswers()->getRelated());
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function test_indictment_made_returns_false_when_indictment_time_is_null(): void
    {
        $team = Team::factory()->withGame()->create();
        $team->indictment_time = null;

        $this->assertFalse($team->indictment_made);
    }

    public function test_indictment_made_returns_true_when_indictment_time_is_set(): void
    {
        $team = Team::factory()->withGame()->create(['indictment_time' => Carbon::now()]);

        $this->assertTrue($team->indictment_made);
    }

    public function test_indictment_correct_returns_true_when_team_solution_matches_game_solution(): void
    {
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $evidence = Evidence::factory()->create();

        $game = Game::factory()->create([
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $team = Team::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $this->assertTrue($team->indictment_correct);
    }

    public function test_indictment_correct_returns_false_when_team_solution_does_not_match_game_solution(): void
    {
        $gameSuspect = Suspect::factory()->create();
        $teamSuspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $evidence = Evidence::factory()->create();

        $game = Game::factory()->create([
            'suspect_id' => $gameSuspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $team = Team::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $teamSuspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $this->assertFalse($team->indictment_correct);
    }

    public function test_game_status_includes_quest_indictment_and_evidence_entries(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'evidence_id' => 0]);
        $team->indictment_time = null;

        $suspect = Suspect::factory()->create(['name' => 'Suspect Name', 'machine' => 'scarlet']);
        Quest::factory()->create(['game_id' => $game->id, 'suspect_id' => $suspect->id]);

        $status = $team->game_status;

        $this->assertInstanceOf(Collection::class, $status);
        $this->assertCount(3, $status);
        $this->assertSame('Suspect Name', $status[0]['name']);
        $this->assertSame('empty', $status[0]['color']);
        $this->assertSame('Indictment', $status[1]['name']);
        $this->assertSame('empty', $status[1]['color']);
        $this->assertSame('Evidence Room', $status[2]['name']);
        $this->assertSame('empty', $status[2]['color']);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function registered_scope_returns_only_non_waitlist_teams(): void
    {
        $registered = Team::factory()->withGame()->create(['waitlist' => false]);
        Team::factory()->withGame()->create(['waitlist' => true]);

        $results = Team::query()->registered()->get();

        $this->assertCount(1, $results);
        $this->assertSame($registered->id, $results->first()->id);
    }

    public function waitlist_scope_returns_only_waitlisted_teams(): void
    {
        $waitlisted = Team::factory()->withGame()->create(['waitlist' => true]);
        Team::factory()->withGame()->create(['waitlist' => false]);

        $results = Team::query()->waitlist()->get();

        $this->assertCount(1, $results);
        $this->assertSame($waitlisted->id, $results->first()->id);
    }

    public function active_scope_returns_teams_belonging_to_active_games_only(): void
    {
        $activeGame = Game::factory()->create(['active' => true]);
        $inactiveGame = Game::factory()->create(['active' => false]);

        $activeTeam = Team::factory()->create(['game_id' => $activeGame->id]);
        Team::factory()->create(['game_id' => $inactiveGame->id]);

        $results = Team::query()->active()->get();

        $this->assertCount(1, $results);
        $this->assertSame($activeTeam->id, $results->first()->id);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function check_player_warnings_returns_false_when_all_warning_collections_are_empty(): void
    {
        $team = Team::factory()->withGame()->create();

        $player = new class
        {
            public array $warnings;

            public function __construct()
            {
                $this->warnings = [collect(), collect()];
            }
        };

        $team->setRelation('players', collect([$player]));

        $this->assertFalse($team->checkPlayerWarnings());
    }

    public function check_player_warnings_returns_true_when_any_warning_collection_has_values(): void
    {
        $team = Team::factory()->withGame()->create();

        $playerA = new class
        {
            public array $warnings;

            public function __construct()
            {
                $this->warnings = [collect(), collect()];
            }
        };

        $playerB = new class
        {
            public array $warnings;

            public function __construct()
            {
                $this->warnings = [collect(['warning'])];
            }
        };

        $team->setRelation('players', collect([$playerA, $playerB]));

        $this->assertTrue($team->checkPlayerWarnings());
    }
}

