<?php

namespace Tests\Unit;

use App\Game;
use App\GhostDna;
use App\Team;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GhostDnaTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $ghostDna = new GhostDna();

        $this->assertEquals(['sequence', 'pair'], $ghostDna->getFillable());
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_a_teams_belongs_to_many_relationship(): void
    {
        $ghostDna = new GhostDna();

        $this->assertInstanceOf(BelongsToMany::class, $ghostDna->teams());
    }

    public function teams_relationship_uses_correct_related_model(): void
    {
        $ghostDna = new GhostDna();

        $this->assertInstanceOf(Team::class, $ghostDna->teams()->getRelated());
    }

    public function teams_relationship_uses_timestamps_on_pivot(): void
    {
        $ghostDna = new GhostDna();
        $relation = $ghostDna->teams();

        $this->assertTrue($relation->hasPivotColumn('created_at'));
        $this->assertTrue($relation->hasPivotColumn('updated_at'));
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function found_stats_groups_found_teams_by_game_name(): void
    {
        $gameA = Game::factory()->create(['name' => 'Game A']);
        $gameB = Game::factory()->create(['name' => 'Game B']);

        $teamA1 = Team::factory()->create(['game_id' => $gameA->id]);
        $teamA2 = Team::factory()->create(['game_id' => $gameA->id]);
        $teamB1 = Team::factory()->create(['game_id' => $gameB->id]);

        $ghostDna = GhostDna::factory()->create();
        $ghostDna->teams()->attach([$teamA1->id, $teamA2->id, $teamB1->id]);

        $stats = $ghostDna->fresh()->found_stats;

        $this->assertTrue($stats->has('Game A'));
        $this->assertTrue($stats->has('Game B'));
        $this->assertCount(2, $stats->get('Game A'));
        $this->assertCount(1, $stats->get('Game B'));
        $this->assertEqualsCanonicalizing([$teamA1->id, $teamA2->id], $stats->get('Game A')->pluck('id')->all());
        $this->assertEquals([$teamB1->id], $stats->get('Game B')->pluck('id')->all());
    }

    public function found_stats_returns_empty_collection_when_no_teams_found(): void
    {
        $ghostDna = GhostDna::factory()->create();

        $stats = $ghostDna->found_stats;

        $this->assertCount(0, $stats);
    }
}

