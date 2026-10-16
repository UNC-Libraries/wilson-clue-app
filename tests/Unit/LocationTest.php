<?php

namespace Tests\Unit;

use App\Location;
use App\Quest;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $location = new Location();

        $this->assertEquals([
            'name',
            'floor',
            'description',
            'map_section',
        ], $location->getFillable());
    }

    public function test_it_can_be_mass_assigned_fillable_fields(): void
    {
        $location = new Location([
            'name' => 'Library',
            'floor' => 2,
            'description' => 'Main stacks',
            'map_section' => 'north',
        ]);

        $this->assertEquals('Library', $location->name);
        $this->assertEquals(2, $location->floor);
        $this->assertEquals('Main stacks', $location->description);
        $this->assertEquals('north', $location->map_section);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_a_quests_has_many_relationship(): void
    {
        $location = new Location();

        $this->assertInstanceOf(HasMany::class, $location->quests());
    }

    public function quests_relationship_uses_expected_related_model_and_foreign_key(): void
    {
        $location = new Location();

        $this->assertInstanceOf(Quest::class, $location->quests()->getRelated());
        $this->assertEquals('location_id', $location->quests()->getForeignKeyName());
    }

    public function test_it_loads_only_quests_for_the_given_location(): void
    {
        $location = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $questA = Quest::factory()->create(['location_id' => $location->id]);
        $questB = Quest::factory()->create(['location_id' => $location->id]);
        Quest::factory()->create(['location_id' => $otherLocation->id]);

        $questIds = $location->quests->pluck('id')->all();

        $this->assertCount(2, $location->quests);
        $this->assertEqualsCanonicalizing([$questA->id, $questB->id], $questIds);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function test_floor_nth_returns_first_second_and_third_suffixes(): void
    {
        $this->assertEquals('1st', Location::factory()->make(['floor' => 1])->floor_nth);
        $this->assertEquals('2nd', Location::factory()->make(['floor' => 2])->floor_nth);
        $this->assertEquals('3rd', Location::factory()->make(['floor' => 3])->floor_nth);
    }

    public function test_floor_nth_returns_th_for_teen_exceptions(): void
    {
        $this->assertEquals('11th', Location::factory()->make(['floor' => 11])->floor_nth);
        $this->assertEquals('12th', Location::factory()->make(['floor' => 12])->floor_nth);
        $this->assertEquals('13th', Location::factory()->make(['floor' => 13])->floor_nth);
    }

    public function test_floor_nth_returns_expected_suffix_for_twenty_series_values(): void
    {
        $this->assertEquals('21st', Location::factory()->make(['floor' => 21])->floor_nth);
        $this->assertEquals('22nd', Location::factory()->make(['floor' => 22])->floor_nth);
        $this->assertEquals('23rd', Location::factory()->make(['floor' => 23])->floor_nth);
        $this->assertEquals('24th', Location::factory()->make(['floor' => 24])->floor_nth);
    }
}

