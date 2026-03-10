<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\GhostDna;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GhostDnaControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): GhostDnaControllerTest
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_displays_all_ghost_dna_grouped_by_pair(): void
    {
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);
        GhostDna::factory()->create(['sequence' => 'gghhss', 'pair' => 2]);
        GhostDna::factory()->create(['sequence' => 'hhggtt', 'pair' => 2]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.ghostDna.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ghostDna.index');
        $response->assertViewHas('pairs', function ($pairs) {
            return $pairs->count() === 2
                && $pairs->has(1)
                && $pairs->has(2)
                && $pairs->get(1)->count() === 2
                && $pairs->get(2)->count() === 2;
        });
    }

    public function test_index_orders_dna_by_pair_number(): void
    {
        GhostDna::factory()->create(['sequence' => 'ttssgg', 'pair' => 3]);
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        GhostDna::factory()->create(['sequence' => 'gghhss', 'pair' => 2]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.ghostDna.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pairs', function ($pairs) {
            $keys = $pairs->keys()->toArray();
            return $keys[0] === 1 && $keys[1] === 2 && $keys[2] === 3;
        });
    }

    public function test_index_returns_empty_collection_when_no_ghost_dna_exists(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.ghostDna.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pairs', function ($pairs) {
            return $pairs->isEmpty();
        });
    }

    public function test_index_groups_matching_pairs_together(): void
    {
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.ghostDna.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pairs', function ($pairs) {
            $pair1 = $pairs->get(1);
            return $pair1->count() === 2
                && $pair1->pluck('sequence')->contains('ghstgh')
                && $pair1->pluck('sequence')->contains('hsghst');
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_dna_pair_with_matching_sequence(): void
    {
        GhostDna::factory()->create(['sequence' => 'existing', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgh',
            ]);

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'DNA pair added!');

        $this->assertDatabaseHas('ghost_dnas', [
            'sequence' => 'ghstgh',
            'pair' => 2,
        ]);

        $this->assertDatabaseHas('ghost_dnas', [
            'sequence' => 'hgtshg',
            'pair' => 2,
        ]);
    }

    public function test_store_generates_correct_matching_sequence_for_g_to_h(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'gggggg',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'gggggg']);
    }

    public function test_store_generates_correct_matching_sequence_for_h_to_g(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'hhhhhh',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'hhhhhh']);
    }

    public function test_store_generates_correct_matching_sequence_for_t_to_s(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'tttttt',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'tttttt']);
    }

    public function test_store_generates_correct_matching_sequence_for_s_to_t(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ssssss',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'ssssss']);
    }

    public function test_store_generates_matching_sequence_for_mixed_characters(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgh',
            ]);

        $response->assertRedirect(route('admin.ghostDna.index'));

        $this->assertDatabaseHas('ghost_dnas', [
            'sequence' => 'hgtshg',
        ]);
    }

    public function test_store_creates_pair_1_when_no_existing_dna(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgh',
            ]);

        $response->assertStatus(500);
        $this->assertDatabaseCount('ghost_dnas', 0);
    }

    public function test_store_validates_sequence_contains_only_valid_characters(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgx',
            ]);

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ghost_dnas', ['sequence' => 'ghstgx']);
    }

    public function test_store_accepts_all_valid_characters(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghsthg',
            ]);

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ghost_dnas', ['sequence' => 'ghsthg', 'pair' => 2]);
    }

    public function test_store_requires_sequence_field(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), []);

        $response->assertSessionHasErrors('sequence');
    }

    public function test_store_validates_sequence_exactly_6_characters(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghst',
            ]);

        $response->assertSessionHasErrors('sequence');
    }

    public function test_store_validates_sequence_not_more_than_6_characters(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstghst',
            ]);

        $response->assertSessionHasErrors('sequence');
    }

    public function test_store_rejects_sequence_missing_h_s_and_t_under_current_validator(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'gggggg',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'gggggg']);
    }

    public function test_store_rejects_sequence_missing_g_s_and_t_under_current_validator(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'hhhhhh',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'hhhhhh']);
    }

    public function test_store_rejects_sequence_missing_g_h_and_s_under_current_validator(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'tttttt',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'tttttt']);
    }

    public function test_store_rejects_sequence_missing_g_h_and_t_under_current_validator(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ssssss',
            ]);

        $response->assertSessionHasErrors('sequence');
        $this->assertDatabaseMissing('ghost_dnas', ['sequence' => 'ssssss']);
    }

    public function test_store_requires_an_existing_pair_before_creating_new_dna(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgh',
            ]);

        $response->assertStatus(500);
        $this->assertDatabaseCount('ghost_dnas', 0);
    }

    public function test_store_validates_sequence_is_unique(): void
    {
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgh',
            ]);

        $response->assertSessionHasErrors('sequence');
    }

    public function test_store_currently_accepts_sequence_with_extra_invalid_character_when_all_required_characters_are_present(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghstgx',
            ]);

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ghost_dnas', ['sequence' => 'ghstgx']);
    }

    public function test_store_accepts_all_valid_characters_when_a_previous_pair_exists(): void
    {
        GhostDna::factory()->create(['sequence' => 'base', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.ghostDna.store'), [
                'sequence' => 'ghsthg',
            ]);

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ghost_dnas', ['sequence' => 'ghsthg', 'pair' => 2]);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_dna_pair_when_not_found_by_teams(): void
    {
        $dna1 = GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        $dna2 = GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'DNA pair deleted!');

        $this->assertDatabaseMissing('ghost_dnas', ['id' => $dna1->id]);
        $this->assertDatabaseMissing('ghost_dnas', ['id' => $dna2->id]);
    }

    public function test_destroy_deletes_both_sequences_in_pair(): void
    {
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));

        $this->assertDatabaseCount('ghost_dnas', 0);
    }

    public function test_destroy_only_deletes_specified_pair(): void
    {
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);
        $keep1 = GhostDna::factory()->create(['sequence' => 'gghhss', 'pair' => 2]);
        $keep2 = GhostDna::factory()->create(['sequence' => 'hhggtt', 'pair' => 2]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));

        $this->assertDatabaseMissing('ghost_dnas', ['pair' => 1]);
        $this->assertDatabaseHas('ghost_dnas', ['id' => $keep1->id]);
        $this->assertDatabaseHas('ghost_dnas', ['id' => $keep2->id]);
    }

    public function test_destroy_prevents_deletion_when_one_sequence_found_by_team(): void
    {
        $dna1 = GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        $dna2 = GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);
        $team = Team::factory()->create(['waitlist' => false]);

        $dna1->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'warning');
        $response->assertSessionHas('alert.message', function ($message) {
            return str_contains($message, 'Cannot delete this pair')
                && str_contains($message, 'found in a previous game')
                && str_contains($message, 'affect the team\'s score');
        });

        $this->assertDatabaseHas('ghost_dnas', ['id' => $dna1->id]);
        $this->assertDatabaseHas('ghost_dnas', ['id' => $dna2->id]);
    }

    public function test_destroy_prevents_deletion_when_both_sequences_found_by_teams(): void
    {
        $dna1 = GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        $dna2 = GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);
        $team1 = Team::factory()->create(['waitlist' => false]);
        $team2 = Team::factory()->create(['waitlist' => false]);

        $dna1->teams()->attach($team1->id);
        $dna2->teams()->attach($team2->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'warning');

        $this->assertDatabaseHas('ghost_dnas', ['id' => $dna1->id]);
        $this->assertDatabaseHas('ghost_dnas', ['id' => $dna2->id]);
    }

    public function test_destroy_prevents_deletion_when_sequence_found_by_multiple_teams(): void
    {
        $dna1 = GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        $dna2 = GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);
        $team1 = Team::factory()->create(['waitlist' => false]);
        $team2 = Team::factory()->create(['waitlist' => false]);

        $dna1->teams()->attach([$team1->id, $team2->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'warning');

        $this->assertDatabaseHas('ghost_dnas', ['id' => $dna1->id]);
        $this->assertDatabaseHas('ghost_dnas', ['id' => $dna2->id]);
    }

    public function test_destroy_eager_loads_teams_relationship(): void
    {
        $dna1 = GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);
        $dna2 = GhostDna::factory()->create(['sequence' => 'hsghst', 'pair' => 1]);
        $team = Team::factory()->create(['waitlist' => false]);

        $dna1->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'warning');
    }

    public function test_destroy_handles_nonexistent_pair(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 999));

        $response->assertRedirect(route('admin.ghostDna.index'));
    }

    public function test_destroy_handles_pair_with_only_one_sequence(): void
    {
        GhostDna::factory()->create(['sequence' => 'ghstgh', 'pair' => 1]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.ghostDna.destroy', 1));

        $response->assertRedirect(route('admin.ghostDna.index'));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseMissing('ghost_dnas', ['pair' => 1]);
    }
}

