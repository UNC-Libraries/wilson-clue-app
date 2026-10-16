<?php

namespace Tests\Unit;

use App\Alert;
use App\Evidence;
use App\Game;
use App\Location;
use App\Quest;
use App\Suspect;
use App\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    
    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $game = new Game();

        $this->assertEquals([
            'name',
            'suspect_id',
            'location_id',
            'evidence_id',
            'max_teams',
            'winning_team',
            'start_time',
            'end_time',
            'registration',
            'flickr',
            'flickr_start_img',
            'special_thanks',
            'team_accolades',
            'archive',
            'case_file_items',
            'created_at',
            'updated_at',
            'evidence_location_id',
            'active',
            'students_only',
        ], $game->getFillable());
    }

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    
    public function test_it_casts_start_time_to_datetime(): void
    {
        $game = Game::factory()->create(['start_time' => '2024-01-01 10:00:00']);

        $this->assertInstanceOf(Carbon::class, $game->start_time);
    }

    
    public function test_it_casts_end_time_to_datetime(): void
    {
        $game = Game::factory()->create(['end_time' => '2024-01-01 18:00:00']);

        $this->assertInstanceOf(Carbon::class, $game->end_time);
    }

    
    public function test_it_casts_active_to_boolean(): void
    {
        $game = Game::factory()->create(['active' => 1]);

        $this->assertIsBool($game->active);
        $this->assertTrue($game->active);
    }

    
    public function test_it_casts_students_only_to_boolean(): void
    {
        $game = Game::factory()->create(['students_only' => 1]);

        $this->assertIsBool($game->students_only);
        $this->assertTrue($game->students_only);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    
    public function test_it_has_many_teams(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasMany::class, $game->teams());
        $this->assertInstanceOf(Team::class, $game->teams()->getRelated());
    }

    
    public function test_it_has_many_registered_teams(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasMany::class, $game->registeredTeams());
    }

    
    public function test_it_has_many_waitlist_teams(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasMany::class, $game->waitlistTeams());
    }

    
    public function test_it_has_one_winning_team(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasOne::class, $game->winningTeam());
        $this->assertInstanceOf(Team::class, $game->winningTeam()->getRelated());
    }

    
    public function test_it_belongs_to_an_evidence_location(): void
    {
        $game = new Game();

        $this->assertInstanceOf(BelongsTo::class, $game->evidenceLocation());
        $this->assertInstanceOf(Location::class, $game->evidenceLocation()->getRelated());
    }

    
    public function test_it_belongs_to_a_geographic_investigation_location(): void
    {
        $game = new Game();

        $this->assertInstanceOf(BelongsTo::class, $game->geographicInvestigationLocation());
        $this->assertInstanceOf(Location::class, $game->geographicInvestigationLocation()->getRelated());
    }

    
    public function test_it_has_one_solution_suspect(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasOne::class, $game->solutionSuspect());
        $this->assertInstanceOf(Suspect::class, $game->solutionSuspect()->getRelated());
    }

    
    public function test_it_has_one_solution_location(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasOne::class, $game->solutionLocation());
        $this->assertInstanceOf(Location::class, $game->solutionLocation()->getRelated());
    }

    
    public function test_it_has_one_solution_evidence(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasOne::class, $game->solutionEvidence());
        $this->assertInstanceOf(Evidence::class, $game->solutionEvidence()->getRelated());
    }

    
    public function test_it_has_many_quests(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasMany::class, $game->quests());
        $this->assertInstanceOf(Quest::class, $game->quests()->getRelated());
    }

    
    public function test_it_belongs_to_many_evidence(): void
    {
        $game = new Game();

        $this->assertInstanceOf(BelongsToMany::class, $game->evidence());
        $this->assertInstanceOf(Evidence::class, $game->evidence()->getRelated());
    }

    
    public function test_it_has_many_alerts(): void
    {
        $game = new Game();

        $this->assertInstanceOf(HasMany::class, $game->alerts());
        $this->assertInstanceOf(Alert::class, $game->alerts()->getRelated());
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    
    public function active_scope_returns_only_active_games(): void
    {
        Game::factory()->create(['active' => true]);
        Game::factory()->create(['active' => false]);

        $games = Game::active()->get();

        $this->assertCount(1, $games);
        $this->assertTrue($games->first()->active);
    }

    
    public function in_progress_scope_returns_games_currently_running(): void
    {
        Game::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time'   => Carbon::now()->addHour(),
        ]);
        Game::factory()->create([
            'start_time' => Carbon::now()->addHour(),
            'end_time'   => Carbon::now()->addHours(2),
        ]);
        Game::factory()->create([
            'start_time' => Carbon::now()->subHours(2),
            'end_time'   => Carbon::now()->subHour(),
        ]);

        $games = Game::inProgress()->get();

        $this->assertCount(1, $games);
    }

    
    public function archived_scope_returns_only_archived_games(): void
    {
        Game::factory()->create(['archive' => true]);
        Game::factory()->create(['archive' => false]);

        $games = Game::archived()->get();

        $this->assertCount(1, $games);
        $this->assertEquals(1, $games->first()->archive);
    }

    
    public function archived_scope_orders_by_start_time_descending(): void
    {
        $older = Game::factory()->create(['archive' => true, 'start_time' => Carbon::now()->subDays(10)]);
        $newer = Game::factory()->create(['archive' => true, 'start_time' => Carbon::now()->subDay()]);

        $games = Game::archived()->get();

        $this->assertEquals($newer->id, $games->first()->id);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    
    public function get_spots_left_attribute_returns_correct_count(): void
    {
        $game = Game::factory()->create(['max_teams' => 10]);
        Team::factory()->count(3)->create(['game_id' => $game->id, 'waitlist' => false]);
        Team::factory()->count(2)->create(['game_id' => $game->id, 'waitlist' => true]);

        $this->assertEquals(7, $game->spots_left);
    }

    
    public function get_spots_left_attribute_ignores_waitlisted_teams(): void
    {
        $game = Game::factory()->create(['max_teams' => 5]);
        Team::factory()->count(5)->create(['game_id' => $game->id, 'waitlist' => true]);

        $this->assertEquals(5, $game->spots_left);
    }

    
    public function get_in_progress_attribute_returns_true_when_game_is_in_progress(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time'   => Carbon::now()->addHour(),
        ]);

        $this->assertTrue($game->inProgress);
    }

    
    public function get_in_progress_attribute_returns_false_when_game_has_not_started(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->addHour(),
            'end_time'   => Carbon::now()->addHours(2),
        ]);

        $this->assertFalse($game->inProgress);
    }

    
    public function get_in_progress_attribute_returns_false_when_game_has_ended(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->subHours(2),
            'end_time'   => Carbon::now()->subHour(),
        ]);

        $this->assertFalse($game->inProgress);
    }

    
    public function get_status_text_attribute_returns_in_progress(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time'   => Carbon::now()->addHour(),
            'active'     => true,
        ]);

        $this->assertEquals('In Progress', $game->statusText);
    }

    
    public function get_status_text_attribute_returns_current_active_when_active_but_not_in_progress(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->addHour(),
            'end_time'   => Carbon::now()->addHours(2),
            'active'     => true,
        ]);

        $this->assertEquals('Current (active)', $game->statusText);
    }

    
    public function get_status_text_attribute_returns_archived_when_archived(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->subHours(2),
            'end_time'   => Carbon::now()->subHour(),
            'active'     => false,
            'archive'    => true,
        ]);

        $this->assertEquals('Archived', $game->statusText);
    }

    
    public function get_status_text_attribute_returns_dormant_when_inactive_and_not_archived(): void
    {
        $game = Game::factory()->create([
            'start_time' => Carbon::now()->subHours(2),
            'end_time'   => Carbon::now()->subHour(),
            'active'     => false,
            'archive'    => false,
        ]);

        $this->assertEquals('Dormant', $game->statusText);
    }

    
    public function get_solution_attribute_returns_correct_array(): void
    {
        $game = Game::factory()->create([
            'suspect_id'  => 1,
            'location_id' => 2,
            'evidence_id' => 3,
        ]);

        $this->assertEquals([
            'suspect'  => 1,
            'location' => 2,
            'evidence' => 3,
        ], $game->solution);
    }

    
    public function get_case_file_items_attribute_decodes_json(): void
    {
        $items = ['item1', 'item2', 'item3'];
        $game  = Game::factory()->create(['case_file_items' => json_encode($items)]);

        $this->assertEquals($items, $game->case_file_items);
    }

    // -------------------------------------------------------------------------
    // Mutators
    // -------------------------------------------------------------------------

    
    public function set_case_file_items_attribute_encodes_to_json(): void
    {
        $items = ['item1', 'item2'];
        $game  = new Game();
        $game->case_file_items = $items;

        $this->assertEquals(json_encode($items), $game->getAttributes()['case_file_items']);
    }

    // -------------------------------------------------------------------------
    // SoftDeletes
    // -------------------------------------------------------------------------

    
    public function test_it_soft_deletes(): void
    {
        $game = Game::factory()->create();

        $game->delete();

        $this->assertSoftDeleted('games', ['id' => $game->id]);
    }

    
    public function soft_deleted_games_are_excluded_from_queries(): void
    {
        $game = Game::factory()->create();

        $game->delete();

        $this->assertNull(Game::find($game->id));
    }

    
    public function soft_deleted_games_can_be_restored(): void
    {
        $game = Game::factory()->create();

        $game->delete();
        $game->restore();

        $this->assertNotNull(Game::find($game->id));
    }
}

