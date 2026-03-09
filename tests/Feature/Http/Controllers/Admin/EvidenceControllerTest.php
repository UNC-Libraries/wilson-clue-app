<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Evidence;
use App\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceControllerTest extends TestCase
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

    public function test_index_displays_all_evidence(): void
    {
        $evidenceA = Evidence::factory()->create(['title' => 'Evidence A']);
        $evidenceB = Evidence::factory()->create(['title' => 'Evidence B']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.index'));

        $response->assertStatus(200);
        $response->assertViewIs('evidence.index');
        $response->assertViewHas('evidence', function ($evidence) use ($evidenceA, $evidenceB) {
            return $evidence->pluck('id')->contains($evidenceA->id)
                && $evidence->pluck('id')->contains($evidenceB->id);
        });
    }

    public function test_index_returns_empty_collection_when_no_evidence_exists(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.index'));

        $response->assertStatus(200);
        $response->assertViewHas('evidence', function ($evidence) {
            return $evidence->isEmpty();
        });
    }

    // -------------------------------------------------------------------------
    // getEvidence
    // -------------------------------------------------------------------------

    public function test_get_evidence_returns_json_list_of_all_evidence(): void
    {
        $evidence1 = Evidence::factory()->create(['title' => 'Evidence 1']);
        $evidence2 = Evidence::factory()->create(['title' => 'Evidence 2']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.getEvidence'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'title', 'description', 'src', 'created_at', 'updated_at']
        ]);
    }

    public function test_get_evidence_filters_by_game_id(): void
    {
        $game = Game::factory()->create();
        $evidence1 = Evidence::factory()->create(['title' => 'Game Evidence']);
        $evidence2 = Evidence::factory()->create(['title' => 'Other Evidence']);

        $game->evidence()->attach($evidence1->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.getEvidence', ['game_id' => $game->id]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals($evidence1->id, $data[0]['id']);
    }

    public function test_get_evidence_excludes_specified_evidence_ids(): void
    {
        $evidence1 = Evidence::factory()->create(['title' => 'Evidence 1']);
        $evidence2 = Evidence::factory()->create(['title' => 'Evidence 2']);
        $evidence3 = Evidence::factory()->create(['title' => 'Evidence 3']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.getEvidence', [
                'exclude_evidence' => "{$evidence1->id},{$evidence2->id}"
            ]));

        $response->assertStatus(200);
        $data = $response->json();

        $ids = collect($data)->pluck('id')->toArray();
        $this->assertNotContains($evidence1->id, $ids);
        $this->assertNotContains($evidence2->id, $ids);
        $this->assertContains($evidence3->id, $ids);
    }

    public function test_get_evidence_combines_game_filter_and_exclude(): void
    {
        $game = Game::factory()->create();
        $evidence1 = Evidence::factory()->create(['title' => 'Game Evidence 1']);
        $evidence2 = Evidence::factory()->create(['title' => 'Game Evidence 2']);
        $evidence3 = Evidence::factory()->create(['title' => 'Other Evidence']);

        $game->evidence()->attach([$evidence1->id, $evidence2->id]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.getEvidence', [
                'game_id' => $game->id,
                'exclude_evidence' => $evidence1->id
            ]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals($evidence2->id, $data[0]['id']);
    }

    public function test_get_evidence_returns_empty_when_all_excluded(): void
    {
        $evidence1 = Evidence::factory()->create();
        $evidence2 = Evidence::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.getEvidence', [
                'exclude_evidence' => "{$evidence1->id},{$evidence2->id}"
            ]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(0, $data);
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_displays_form_with_new_evidence(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.create'));

        $response->assertStatus(200);
        $response->assertViewIs('evidence.create');
        $response->assertViewHas('evidence', function ($evidence) {
            return $evidence instanceof Evidence && !$evidence->exists;
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_evidence_with_required_fields(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'Test Evidence',
                'description' => 'Test Description',
            ]);

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Test Evidence created!');

        $this->assertDatabaseHas('evidence', [
            'title' => 'Test Evidence',
            'description' => 'Test Description',
        ]);
    }

    public function test_store_creates_evidence_with_uploaded_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('evidence.jpg', 800, 600);

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'Evidence with Image',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'success');

        $evidence = Evidence::where('title', 'Evidence with Image')->first();
        $this->assertNotNull($evidence->src);
        Storage::disk('public')->assertExists($evidence->src);
    }

    public function test_store_validates_image_file_size(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('large-evidence.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'Evidence with Large Image',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_validates_image_mime_type(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'Evidence with PDF',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_accepts_jpeg_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'JPEG Evidence',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_accepts_png_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.png');

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'PNG Evidence',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_accepts_svg_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('test.svg', 10, 'image/svg+xml');

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'SVG Evidence',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_requires_title_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'description' => 'Description',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_creates_evidence_without_file(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'No File Evidence',
                'description' => 'Description',
            ]);

        $response->assertRedirect(route('admin.evidence.index'));

        $evidence = Evidence::where('title', 'No File Evidence')->first();
        $this->assertNotNull($evidence);
        $this->assertNull($evidence->getAttributes()['src']);
    }

    public function test_store_stores_file_in_evidence_directory(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAsAdmin()
            ->post(route('admin.evidence.store'), [
                'title' => 'Directory Test',
                'description' => 'Description',
                'new_image_file' => $file,
            ]);

        $evidence = Evidence::where('title', 'Directory Test')->first();
        $this->assertStringStartsWith('evidence/', $evidence->src);
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_form_with_existing_evidence(): void
    {
        $evidence = Evidence::factory()->create([
            'title' => 'Edit Test',
            'description' => 'Test Description',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.edit', $evidence->id));

        $response->assertStatus(200);
        $response->assertViewIs('evidence.edit');
        $response->assertViewHas('evidence', function ($e) use ($evidence) {
            return $e->id === $evidence->id
                && $e->title === 'Edit Test'
                && $e->description === 'Test Description';
        });
    }

    public function test_edit_returns_404_for_nonexistent_evidence(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.evidence.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_evidence(): void
    {
        $evidence = Evidence::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', $evidence->id), [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ]);

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Updated Title updated!');

        $this->assertDatabaseHas('evidence', [
            'id' => $evidence->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);
    }

    public function test_update_replaces_image_file_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('evidence', 'public');

        $evidence = Evidence::factory()->create(['src' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', $evidence->id), [
                'title' => $evidence->title,
                'description' => $evidence->description,
                'new_image_file' => $newFile,
            ]);

        $response->assertRedirect(route('admin.evidence.index'));

        $fresh = $evidence->fresh();
        $this->assertNotEquals($oldPath, $fresh->src);
        Storage::disk('public')->assertExists($fresh->src);
    }

    public function test_update_validates_new_image_file_size(): void
    {
        Storage::fake('public');

        $evidence = Evidence::factory()->create();
        $file = UploadedFile::fake()->create('large.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', $evidence->id), [
                'title' => $evidence->title,
                'description' => $evidence->description,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_update_validates_new_image_mime_type(): void
    {
        Storage::fake('public');

        $evidence = Evidence::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', $evidence->id), [
                'title' => $evidence->title,
                'description' => $evidence->description,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_update_without_new_file_preserves_existing_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('evidence', 'public');

        $evidence = Evidence::factory()->create(['src' => $oldPath]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', $evidence->id), [
                'title' => 'Updated Title',
                'description' => $evidence->description,
            ]);

        $response->assertRedirect(route('admin.evidence.index'));

        $fresh = $evidence->fresh();
        $this->assertEquals($oldPath, $fresh->src);
        Storage::disk('public')->assertExists($oldPath);
    }

    public function test_update_requires_title_field(): void
    {
        $evidence = Evidence::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', $evidence->id), [
                'description' => 'Description',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_update_returns_404_for_nonexistent_evidence(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.evidence.update', 999999), [
                'title' => 'Test',
                'description' => 'Description',
            ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_evidence_without_attached_games(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('evidence', 'public');

        $evidence = Evidence::factory()->create([
            'title' => 'To Delete',
            'src' => $path,
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'To Delete deleted!');

        $this->assertDatabaseMissing('evidence', ['id' => $evidence->id]);
    }

    public function test_destroy_prevents_deletion_when_evidence_has_attached_games(): void
    {
        $evidence = Evidence::factory()->create(['title' => 'Used Evidence']);
        $game = Game::factory()->create();

        $game->evidence()->attach($evidence->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', 'Used Evidence cannot be deleted. It is attached to past games.');

        $this->assertDatabaseHas('evidence', ['id' => $evidence->id]);
    }

    public function test_destroy_deletes_image_file_when_deleting_record(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('evidence', 'public');

        $evidence = Evidence::factory()->create(['src' => $path]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'success');
    }

    public function test_destroy_does_not_delete_image_file_when_games_attached(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('evidence', 'public');

        $evidence = Evidence::factory()->create(['src' => $path]);
        $game = Game::factory()->create();

        $game->evidence()->attach($evidence->id);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'danger');

        Storage::disk('public')->assertExists($path);
    }

    public function test_destroy_returns_404_for_nonexistent_evidence(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', 999999));

        $response->assertStatus(404);
    }

    public function test_destroy_handles_evidence_with_multiple_attached_games(): void
    {
        $evidence = Evidence::factory()->create(['title' => 'Multi Game']);
        $game1 = Game::factory()->create();
        $game2 = Game::factory()->create();

        $game1->evidence()->attach($evidence->id);
        $game2->evidence()->attach($evidence->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'danger');

        $this->assertDatabaseHas('evidence', ['id' => $evidence->id]);
    }

    public function test_destroy_deletes_evidence_without_src_attribute(): void
    {
        $evidence = Evidence::factory()->create([
            'title' => 'No File Evidence',
            'src' => null,
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseMissing('evidence', ['id' => $evidence->id]);
    }

    public function test_destroy_eager_loads_games_relationship(): void
    {
        $evidence = Evidence::factory()->create();
        $game = Game::factory()->create();
        $game->evidence()->attach($evidence->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.evidence.destroy', $evidence->id));

        $response->assertRedirect(route('admin.evidence.index'));
        $response->assertSessionHas('alert.type', 'danger');
    }
}

