<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\Location;
use App\Quest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
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

    public function test_index_displays_all_locations(): void
    {
        $locationA = Location::factory()->create(['name' => 'Library']);
        $locationB = Location::factory()->create(['name' => 'Cafeteria']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.location.index'));

        $response->assertStatus(200);
        $response->assertViewIs('location.index');
        $response->assertViewHas('locations', function ($locations) use ($locationA, $locationB) {
            return $locations->pluck('id')->contains($locationA->id)
                && $locations->pluck('id')->contains($locationB->id);
        });
    }

    public function test_index_returns_empty_collection_when_no_locations_exist(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.location.index'));

        $response->assertStatus(200);
        $response->assertViewHas('locations', function ($locations) {
            return $locations->isEmpty();
        });
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_displays_form_with_new_location_and_map_sections(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.location.create'));

        $response->assertStatus(200);
        $response->assertViewIs('location.create');
        $response->assertViewHas('location', function ($location) {
            return $location instanceof Location && !$location->exists;
        });
        $response->assertViewHas('mapSections');
    }

    public function test_create_loads_map_sections_from_config(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.location.create'));

        $response->assertStatus(200);
        $response->assertViewHas('mapSections', function ($mapSections) {
            return is_array($mapSections) && !empty($mapSections);
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_location_with_required_fields(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Main Hall',
                'floor' => 2,
                'map_section' => 'north',
                'description' => 'The main entrance hall',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Main Hall  created');

        $this->assertDatabaseHas('locations', [
            'name' => 'Main Hall',
            'floor' => 2,
            'map_section' => 'north',
            'description' => 'The main entrance hall',
        ]);
    }

    public function test_store_creates_location_without_optional_description(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Simple Location',
                'floor' => 1,
                'map_section' => 'south',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('locations', [
            'name' => 'Simple Location',
            'floor' => 1,
            'map_section' => 'south',
        ]);
    }

    public function test_store_requires_name_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'floor' => 1,
                'map_section' => 'north',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_requires_floor_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Test Location',
                'map_section' => 'north',
            ]);

        $response->assertSessionHasErrors('floor');
    }

    public function test_store_requires_map_section_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Test Location',
                'floor' => 1,
            ]);

        $response->assertSessionHasErrors('map_section');
    }

    public function test_store_validates_floor_is_integer(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Test Location',
                'floor' => 'not-an-integer',
                'map_section' => 'north',
            ]);

        $response->assertSessionHasErrors('floor');
    }

    public function test_store_accepts_negative_floor_numbers(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Basement',
                'floor' => -1,
                'map_section' => 'basement',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('locations', [
            'name' => 'Basement',
            'floor' => -1,
        ]);
    }

    public function test_store_accepts_zero_floor_number(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'Ground Floor',
                'floor' => 0,
                'map_section' => 'ground',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('locations', [
            'name' => 'Ground Floor',
            'floor' => 0,
        ]);
    }

    public function test_store_redirects_with_location_name_in_success_message(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.location.store'), [
                'name' => 'New Location',
                'floor' => 3,
                'map_section' => 'east',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.message', 'New Location  created');
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_form_with_existing_location(): void
    {
        $location = Location::factory()->create([
            'name' => 'Edit Test',
            'floor' => 2,
            'map_section' => 'west',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.location.edit', $location->id));

        $response->assertStatus(200);
        $response->assertViewIs('location.edit');
        $response->assertViewHas('location', function ($loc) use ($location) {
            return $loc->id === $location->id
                && $loc->name === 'Edit Test'
                && $loc->floor === 2
                && $loc->map_section === 'west';
        });
        $response->assertViewHas('mapSections');
    }

    public function test_edit_loads_map_sections_from_config(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.location.edit', $location->id));

        $response->assertStatus(200);
        $response->assertViewHas('mapSections', function ($mapSections) {
            return is_array($mapSections) && !empty($mapSections);
        });
    }

    public function test_edit_returns_404_for_nonexistent_location(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.location.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_location(): void
    {
        $location = Location::factory()->create([
            'name' => 'Original Name',
            'floor' => 1,
            'map_section' => 'north',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'name' => 'Updated Name',
                'floor' => 3,
                'map_section' => 'south',
                'description' => 'New description',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Updated Name  updated');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Name',
            'floor' => 3,
            'map_section' => 'south',
            'description' => 'New description',
        ]);
    }

    public function test_update_changes_only_name(): void
    {
        $location = Location::factory()->create([
            'name' => 'Original',
            'floor' => 2,
            'map_section' => 'east',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'name' => 'Just Name Changed',
                'floor' => 2,
                'map_section' => 'east',
            ]);

        $response->assertRedirect(route('admin.location.index'));

        $fresh = $location->fresh();
        $this->assertEquals('Just Name Changed', $fresh->name);
        $this->assertEquals(2, $fresh->floor);
        $this->assertEquals('east', $fresh->map_section);
    }

    public function test_update_requires_name_field(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'floor' => 1,
                'map_section' => 'north',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_requires_floor_field(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'name' => 'Test',
                'map_section' => 'north',
            ]);

        $response->assertSessionHasErrors('floor');
    }

    public function test_update_requires_map_section_field(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'name' => 'Test',
                'floor' => 1,
            ]);

        $response->assertSessionHasErrors('map_section');
    }

    public function test_update_validates_floor_is_integer(): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'name' => 'Test',
                'floor' => 'invalid',
                'map_section' => 'north',
            ]);

        $response->assertSessionHasErrors('floor');
    }

    public function test_update_returns_404_for_nonexistent_location(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', 999999), [
                'name' => 'Test',
                'floor' => 1,
                'map_section' => 'north',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_redirects_with_location_name_in_success_message(): void
    {
        $location = Location::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.location.update', $location->id), [
                'name' => 'New Name',
                'floor' => 1,
                'map_section' => 'north',
            ]);

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.message', 'New Name  updated');
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_location_without_quests_or_game_references(): void
    {
        $location = Location::factory()->create(['name' => 'To Delete']);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'To Delete deleted!');

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_destroy_prevents_deletion_when_location_has_quests(): void
    {
        $location = Location::factory()->create(['name' => 'Used Location']);
        $quest = Quest::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', 'Used Location cannot be deleted. It is was used in a previous game.');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);

        $quest; // silence unused variable warning
    }

    public function test_destroy_prevents_deletion_when_location_is_evidence_location(): void
    {
        $location = Location::factory()->create(['name' => 'Evidence Location']);
        $game = Game::factory()->create(['evidence_location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', 'Evidence Location cannot be deleted. It is was used in a previous game.');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);

        $game; // silence unused variable warning
    }

    public function test_destroy_prevents_deletion_when_location_is_geographic_investigation_location(): void
    {
        $location = Location::factory()->create(['name' => 'GeoInv Location']);
        $game = Game::factory()->create(['geographic_investigation_location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', 'GeoInv Location cannot be deleted. It is was used in a previous game.');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);

        $game; // silence unused variable warning
    }

    public function test_destroy_prevents_deletion_when_location_has_multiple_quests(): void
    {
        $location = Location::factory()->create(['name' => 'Multi Quest Location']);
        $quest1 = Quest::factory()->create(['location_id' => $location->id]);
        $quest2 = Quest::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);

        $quest1; // silence unused variable warnings
        $quest2;
    }

    public function test_destroy_prevents_deletion_when_location_has_both_quests_and_game_references(): void
    {
        $location = Location::factory()->create(['name' => 'Fully Used']);
        $quest = Quest::factory()->create(['location_id' => $location->id]);
        $game = Game::factory()->create(['evidence_location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);

        $quest; // silence unused variable warnings
        $game;
    }

    public function test_destroy_prevents_deletion_when_location_is_both_evidence_and_geographic_investigation(): void
    {
        $location = Location::factory()->create(['name' => 'Double Reference']);
        $game = Game::factory()->create([
            'evidence_location_id' => $location->id,
            'geographic_investigation_location_id' => $location->id,
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_location(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', 999999));

        $response->assertStatus(404);
    }

    public function test_destroy_eager_loads_quests_relationship(): void
    {
        $location = Location::factory()->create();
        Quest::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.type', 'danger');
    }

    public function test_destroy_redirects_with_location_name_in_message(): void
    {
        $location = Location::factory()->create(['name' => 'Test Location']);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.location.destroy', $location->id));

        $response->assertRedirect(route('admin.location.index'));
        $response->assertSessionHas('alert.message', function ($message) {
            return str_contains($message, 'Test Location');
        });
    }
}

