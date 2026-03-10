<?php

namespace Tests\Unit;

use App\MinigameImage;
use App\Quest;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MinigameImageTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $image = new MinigameImage();

        $this->assertEquals(['name', 'year', 'src'], $image->getFillable());
    }

    public function test_it_can_be_mass_assigned_fillable_fields(): void
    {
        $image = new MinigameImage([
            'name' => 'Campus Photo',
            'year' => 2001,
            'src' => 'minigames/campus.jpg',
        ]);

        $this->assertEquals('Campus Photo', $image->name);
        $this->assertEquals(2001, $image->year);
        $this->assertEquals(env('PUBLIC_UPLOADS_PATH').'/minigames/campus.jpg', $image->src);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_a_quests_belongs_to_many_relationship(): void
    {
        $image = new MinigameImage();

        $this->assertInstanceOf(BelongsToMany::class, $image->quests());
    }

    public function quests_relationship_uses_expected_related_model(): void
    {
        $image = new MinigameImage();

        $this->assertInstanceOf(Quest::class, $image->quests()->getRelated());
    }

    public function test_it_can_attach_and_detach_quests(): void
    {
        $image = MinigameImage::factory()->create();
        $questA = Quest::factory()->create();
        $questB = Quest::factory()->create();

        $image->quests()->attach([$questA->id, $questB->id]);
        $this->assertCount(2, $image->fresh()->quests);

        $image->quests()->detach($questA->id);
        $this->assertCount(1, $image->fresh()->quests);
        $this->assertEquals($questB->id, $image->fresh()->quests->first()->id);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function test_src_accessor_prepends_public_uploads_path(): void
    {
        $image = MinigameImage::factory()->make(['src' => 'minigames/photo.jpg']);

        $this->assertEquals(env('PUBLIC_UPLOADS_PATH').'/minigames/photo.jpg', $image->src);
    }

    public function test_src_accessor_returns_base_path_when_src_is_null(): void
    {
        $image = new MinigameImage();

        $this->assertEquals(env('PUBLIC_UPLOADS_PATH').'/', $image->src);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function delete_image_deletes_file_when_it_exists(): void
    {
        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('delete')->once();

        $image = MinigameImage::factory()->make(['src' => 'minigames/photo.jpg']);
        $image->deleteImage();
    }

    public function delete_image_does_not_delete_when_file_is_missing(): void
    {
        File::shouldReceive('exists')->once()->andReturn(false);
        File::shouldReceive('delete')->never();

        $image = MinigameImage::factory()->make(['src' => 'minigames/photo.jpg']);
        $image->deleteImage();
    }

    public function delete_image_checks_the_expected_upload_path(): void
    {
        $uploadPath = config('filesystems.disks.public.root');

        File::shouldReceive('exists')
            ->once()
            ->with("$uploadPath/minigames/photo.jpg")
            ->andReturn(true);
        File::shouldReceive('delete')->once();

        $image = MinigameImage::factory()->make(['src' => 'minigames/photo.jpg']);
        $image->deleteImage();
    }
}

