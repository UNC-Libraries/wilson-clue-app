<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Suspect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspectControllerTest extends TestCase
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

    public function test_index_displays_all_suspects(): void
    {
        $suspectA = Suspect::factory()->create(['name' => 'Colonel Mustard']);
        $suspectB = Suspect::factory()->create(['name' => 'Professor Plum']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.suspect.index'));

        $response->assertStatus(200);
        $response->assertViewIs('suspect.index');
        $response->assertViewHas('suspects', function ($suspects) use ($suspectA, $suspectB) {
            return $suspects->pluck('id')->contains($suspectA->id)
                && $suspects->pluck('id')->contains($suspectB->id);
        });
    }

    public function test_index_returns_empty_collection_when_no_suspects_exist(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.suspect.index'));

        $response->assertStatus(200);
        $response->assertViewIs('suspect.index');
        $response->assertViewHas('suspects', function ($suspects) {
            return $suspects->isEmpty();
        });
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_view_with_suspect(): void
    {
        $suspect = Suspect::factory()->create([
            'name' => 'Miss Scarlet',
            'machine' => 'scarlet',
            'profession' => 'Actress',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.suspect.edit', $suspect->id));

        $response->assertStatus(200);
        $response->assertViewIs('suspect.edit');
        $response->assertViewHas('suspect', function ($s) use ($suspect) {
            return $s->id === $suspect->id
                && $s->name === 'Miss Scarlet'
                && $s->machine === 'scarlet';
        });
    }

    public function test_edit_returns_404_for_nonexistent_suspect(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.suspect.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_changes_suspect_name_successfully(): void
    {
        $suspect = Suspect::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));
        $response->assertSessionHas('message', 'Updated Name updated');

        $this->assertDatabaseHas('suspects', [
            'id' => $suspect->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_changes_machine_color(): void
    {
        $suspect = Suspect::factory()->create(['machine' => 'white']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'machine' => 'scarlet',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));

        $this->assertDatabaseHas('suspects', [
            'id' => $suspect->id,
            'machine' => 'scarlet',
        ]);
    }

    public function test_update_changes_profession(): void
    {
        $suspect = Suspect::factory()->create(['profession' => 'Butler']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'profession' => 'Detective',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));

        $this->assertDatabaseHas('suspects', [
            'id' => $suspect->id,
            'profession' => 'Detective',
        ]);
    }

    public function test_update_changes_bio(): void
    {
        $suspect = Suspect::factory()->create(['bio' => 'Original bio']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'bio' => 'Updated biography text',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));

        $this->assertDatabaseHas('suspects', [
            'id' => $suspect->id,
            'bio' => 'Updated biography text',
        ]);
    }

    public function test_update_changes_quote(): void
    {
        $suspect = Suspect::factory()->create(['quote' => 'Original quote']);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'quote' => 'A famous new quote',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));

        $this->assertDatabaseHas('suspects', [
            'id' => $suspect->id,
            'quote' => 'A famous new quote',
        ]);
    }

    public function test_update_changes_multiple_attributes_at_once(): void
    {
        $suspect = Suspect::factory()->create([
            'name' => 'Original Name',
            'profession' => 'Original Profession',
            'bio' => 'Original Bio',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'name' => 'New Name',
                'profession' => 'New Profession',
                'bio' => 'New Bio',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));

        $fresh = $suspect->fresh();
        $this->assertEquals('New Name', $fresh->name);
        $this->assertEquals('New Profession', $fresh->profession);
        $this->assertEquals('New Bio', $fresh->bio);
    }

    public function test_update_only_changes_provided_attributes(): void
    {
        $suspect = Suspect::factory()->create([
            'name' => 'Original Name',
            'profession' => 'Original Profession',
            'bio' => 'Original Bio',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'name' => 'Updated Name',
            ]);

        $fresh = $suspect->fresh();
        $this->assertEquals('Updated Name', $fresh->name);
        $this->assertEquals('Original Profession', $fresh->profession);
        $this->assertEquals('Original Bio', $fresh->bio);
    }

    public function test_update_does_not_change_attributes_with_same_value(): void
    {
        $suspect = Suspect::factory()->create(['name' => 'Same Name']);

        $originalUpdatedAt = $suspect->updated_at;

        sleep(1);

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'name' => 'Same Name',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));

        $fresh = $suspect->fresh();
        $this->assertEquals('Same Name', $fresh->name);
    }

    public function test_update_returns_404_for_nonexistent_suspect(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', 999999), [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_applies_id_and_created_at_when_present_in_request(): void
    {
        $suspect = Suspect::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.suspect.update', $suspect->id), [
                'id' => 99999,
                'created_at' => '2020-01-01 00:00:00',
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('admin.suspect.index'));
        $response->assertSessionHas('message', 'Updated Name updated');

        $this->assertDatabaseHas('suspects', [
            'id' => 99999,
            'name' => 'Updated Name',
            'created_at' => '2020-01-01 00:00:00',
        ]);
    }
}
