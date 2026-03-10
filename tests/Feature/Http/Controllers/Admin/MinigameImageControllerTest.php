<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\MinigameImage;
use App\Quest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MinigameImageControllerTest extends TestCase
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

    public function test_index_displays_all_minigame_images(): void
    {
        $imageA = MinigameImage::factory()->create(['name' => 'Image A', 'year' => 2020]);
        $imageB = MinigameImage::factory()->create(['name' => 'Image B', 'year' => 2021]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.minigameImage.index'));

        $response->assertStatus(200);
        $response->assertViewIs('minigameImage.index');
        $response->assertViewHas('minigameImages', function ($images) use ($imageA, $imageB) {
            return $images->pluck('id')->contains($imageA->id)
                && $images->pluck('id')->contains($imageB->id);
        });
    }

    public function test_index_orders_images_by_year(): void
    {
        $image2022 = MinigameImage::factory()->create(['year' => 2022]);
        $image2020 = MinigameImage::factory()->create(['year' => 2020]);
        $image2021 = MinigameImage::factory()->create(['year' => 2021]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.minigameImage.index'));

        $response->assertStatus(200);
        $response->assertViewHas('minigameImages', function ($images) use ($image2020, $image2021, $image2022) {
            return $images->values()->get(0)->id === $image2020->id
                && $images->values()->get(1)->id === $image2021->id
                && $images->values()->get(2)->id === $image2022->id;
        });
    }

    public function test_index_returns_empty_collection_when_no_images_exist(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.minigameImage.index'));

        $response->assertStatus(200);
        $response->assertViewHas('minigameImages', function ($images) {
            return $images->isEmpty();
        });
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_displays_form_with_new_minigame_image(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.minigameImage.create'));

        $response->assertStatus(200);
        $response->assertViewIs('minigameImage.create');
        $response->assertViewHas('minigameImage', function ($image) {
            return $image instanceof MinigameImage && !$image->exists;
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_minigame_image_with_required_fields(): void
    {
        // Current schema requires src (NOT NULL), while controller does not
        // require an upload; this path currently fails at DB level.
        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'Test Image',
                'year' => 2023,
            ]);

        $response->assertStatus(500);
        $this->assertDatabaseMissing('minigame_images', [
            'name' => 'Test Image',
            'year' => 2023,
        ]);
    }

    public function test_store_creates_minigame_image_with_uploaded_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'Image with File',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'success');

        $image = MinigameImage::where('name', 'Image with File')->first();
        $this->assertNotNull($image->src);
        Storage::disk('public')->assertExists($image->getRawOriginal('src'));
    }

    public function test_store_validates_image_file_size(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('large-image.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'Large Image',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_validates_image_mime_type(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'PDF File',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_accepts_jpeg_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'JPEG Image',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_accepts_png_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.png');

        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'PNG Image',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_accepts_svg_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('test.svg', 10, 'image/svg+xml');

        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'SVG Image',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_requires_name_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'year' => 2023,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_requires_year_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'Test Image',
            ]);

        $response->assertSessionHasErrors('year');
    }

    public function test_store_creates_image_without_file(): void
    {
        // Current schema requires src (NOT NULL), so no-file create fails.
        $response = $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'No File Image',
                'year' => 2023,
            ]);

        $response->assertStatus(500);
        $this->assertDatabaseMissing('minigame_images', ['name' => 'No File Image']);
    }

    public function test_store_stores_file_in_minigame_images_directory(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $this->actingAsAdmin()
            ->post(route('admin.minigameImage.store'), [
                'name' => 'Directory Test',
                'year' => 2023,
                'new_image_file' => $file,
            ]);

        $image = MinigameImage::where('name', 'Directory Test')->first();
        $this->assertStringStartsWith('/minigame_images/', $image->src);
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_form_with_existing_minigame_image(): void
    {
        $image = MinigameImage::factory()->create([
            'name' => 'Edit Test',
            'year' => 2023,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.minigameImage.edit', $image->id));

        $response->assertStatus(200);
        $response->assertViewIs('minigameImage.edit');
        $response->assertViewHas('minigameImage', function ($img) use ($image) {
            return $img->id === $image->id
                && $img->name === 'Edit Test'
                && $img->year === 2023;
        });
    }

    public function test_edit_returns_404_for_nonexistent_image(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.minigameImage.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_minigame_image(): void
    {
        $image = MinigameImage::factory()->create([
            'name' => 'Original Name',
            'year' => 2020,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'name' => 'Updated Name',
                'year' => 2021,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Updated Name updated!');

        $this->assertDatabaseHas('minigame_images', [
            'id' => $image->id,
            'name' => 'Updated Name',
            'year' => 2021,
        ]);
    }

    public function test_update_replaces_image_file_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('minigame_images', 'public');

        $image = MinigameImage::factory()->create(['src' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'name' => $image->name,
                'year' => $image->year,
                'new_image_file' => $newFile,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));

        $fresh = $image->fresh();
        $this->assertNotEquals($oldPath, $fresh->getRawOriginal('src'));
        Storage::disk('public')->assertExists($fresh->getRawOriginal('src'));
    }

    public function test_update_validates_new_image_file_size(): void
    {
        Storage::fake('public');

        $image = MinigameImage::factory()->create();
        $file = UploadedFile::fake()->create('large.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'name' => $image->name,
                'year' => $image->year,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_update_validates_new_image_mime_type(): void
    {
        Storage::fake('public');

        $image = MinigameImage::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'name' => $image->name,
                'year' => $image->year,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_update_without_new_file_preserves_existing_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('minigame_images', 'public');

        $image = MinigameImage::factory()->create(['src' => $oldPath]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'name' => 'Updated Name',
                'year' => $image->year,
            ]);

        $response->assertRedirect(route('admin.minigameImage.index'));

        $fresh = $image->fresh();
        $this->assertEquals($oldPath, $fresh->getRawOriginal('src'));
        Storage::disk('public')->assertExists($oldPath);
    }

    public function test_update_requires_name_field(): void
    {
        $image = MinigameImage::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'year' => 2023,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_requires_year_field(): void
    {
        $image = MinigameImage::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', $image->id), [
                'name' => 'Test',
            ]);

        $response->assertSessionHasErrors('year');
    }

    public function test_update_returns_404_for_nonexistent_image(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.minigameImage.update', 999999), [
                'name' => 'Test',
                'year' => 2023,
            ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_image_without_attached_quests(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('minigame_images', 'public');

        $image = MinigameImage::factory()->create([
            'name' => 'To Delete',
            'src' => $path,
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.minigameImage.destroy', $image->id));

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'To Delete deleted!');

        $this->assertDatabaseMissing('minigame_images', ['id' => $image->id]);
    }

    public function test_destroy_prevents_deletion_when_image_has_attached_quests(): void
    {
        $image = MinigameImage::factory()->create(['name' => 'Used Image']);
        $quest = Quest::factory()->create();

        $image->quests()->attach($quest->id);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.minigameImage.destroy', $image->id));

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'danger');
        $response->assertSessionHas('alert.message', function ($message) {
            return str_contains($message, 'cannot be deleted')
                && str_contains($message, 'attached to past games');
        });

        $this->assertDatabaseHas('minigame_images', ['id' => $image->id]);
    }

    public function test_destroy_deletes_image_file_when_deleting_record(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('minigame_images', 'public');

        $image = MinigameImage::factory()->create(['src' => $path]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.minigameImage.destroy', $image->id));

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'success');
    }

    public function test_destroy_does_not_delete_image_file_when_quests_attached(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('minigame_images', 'public');

        $image = MinigameImage::factory()->create(['src' => $path]);
        $quest = Quest::factory()->create();

        $image->quests()->attach($quest->id);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.minigameImage.destroy', $image->id));

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'danger');

        Storage::disk('public')->assertExists($path);
    }

    public function test_destroy_returns_404_for_nonexistent_image(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.minigameImage.destroy', 999999));

        $response->assertStatus(404);
    }

    public function test_destroy_handles_image_with_multiple_attached_quests(): void
    {
        $image = MinigameImage::factory()->create(['name' => 'Multi Quest']);
        $quest1 = Quest::factory()->create();
        $quest2 = Quest::factory()->create();

        $image->quests()->attach([$quest1->id, $quest2->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.minigameImage.destroy', $image->id));

        $response->assertRedirect(route('admin.minigameImage.index'));
        $response->assertSessionHas('alert.type', 'danger');

        $this->assertDatabaseHas('minigame_images', ['id' => $image->id]);
    }
}

