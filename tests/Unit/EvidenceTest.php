<?php

namespace Tests\Unit;

use App\Evidence;
use App\Game;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EvidenceTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    
    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $evidence = new Evidence();

        $this->assertEquals(['title', 'src'], $evidence->getFillable());
    }

    
    public function test_it_can_be_mass_assigned_title_and_src(): void
    {
        $evidence = new Evidence([
            'title' => 'Test Evidence',
            'src'   => 'evidence/photo.jpg',
        ]);

        $this->assertEquals('Test Evidence', $evidence->title);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    
    public function test_it_has_a_games_belongs_to_many_relationship(): void
    {
        $evidence = new Evidence();

        $this->assertInstanceOf(BelongsToMany::class, $evidence->games());
    }

    
    public function games_relationship_uses_correct_related_model(): void
    {
        $evidence = new Evidence();

        $this->assertInstanceOf(Game::class, $evidence->games()->getRelated());
    }

    
    public function test_it_belongs_to_many_games(): void
    {
        $evidence = Evidence::factory()->create();
        $games    = Game::factory()->count(2)->create();

        $evidence->games()->attach($games->pluck('id'));

        $this->assertCount(2, $evidence->games);
        $this->assertInstanceOf(Game::class, $evidence->games->first());
    }

    
    public function test_it_can_be_detached_from_a_game(): void
    {
        $evidence = Evidence::factory()->create();
        $game     = Game::factory()->create();

        $evidence->games()->attach($game->id);
        $evidence->games()->detach($game->id);

        $this->assertCount(0, $evidence->fresh()->games);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    
    public function test_it_prepends_public_uploads_path_to_src(): void
    {
        $evidence = Evidence::factory()->make(['src' => 'evidence/photo.jpg']);

        $expected = env('PUBLIC_UPLOADS_PATH').'/evidence/photo.jpg';

        $this->assertEquals($expected, $evidence->src);
    }

    
    public function src_accessor_returns_null_when_src_is_null(): void
    {
        $evidence = new Evidence();

        $expected = env('PUBLIC_UPLOADS_PATH').'/';

        $this->assertEquals($expected, $evidence->src);
    }

    // -------------------------------------------------------------------------
    // Database
    // -------------------------------------------------------------------------

    
    public function test_it_can_be_created_in_the_database(): void
    {
        $evidence = Evidence::factory()->create([
            'title' => 'Test Evidence',
        ]);

        $this->assertDatabaseHas('evidence', [
            'id'    => $evidence->id,
            'title' => 'Test Evidence',
        ]);
    }

    
    public function test_it_can_be_updated_in_the_database(): void
    {
        $evidence = Evidence::factory()->create(['title' => 'Original Title']);

        $evidence->update(['title' => 'Updated Title']);

        $this->assertDatabaseHas('evidence', [
            'id'    => $evidence->id,
            'title' => 'Updated Title',
        ]);
    }

    
    public function test_it_can_be_deleted_from_the_database(): void
    {
        $evidence = Evidence::factory()->create();

        $evidence->delete();

        $this->assertDatabaseMissing('evidence', ['id' => $evidence->id]);
    }

    // -------------------------------------------------------------------------
    // deleteImage
    // -------------------------------------------------------------------------

    
    public function delete_image_deletes_file_when_test_it_exists(): void
    {
        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('delete')->once();

        $evidence = Evidence::factory()->make(['src' => 'evidence/photo.jpg']);
        $evidence->deleteImage();
    }

    
    public function delete_image_does_not_call_delete_when_file_does_not_exist(): void
    {
        File::shouldReceive('exists')->once()->andReturn(false);
        File::shouldReceive('delete')->never();

        $evidence = Evidence::factory()->make(['src' => 'evidence/photo.jpg']);
        $evidence->deleteImage();
    }

    
    public function delete_image_uses_correct_upload_path(): void
    {
        $uploadPath = config('filesystems.disks.public.root');

        File::shouldReceive('exists')
            ->once()
            ->with("$uploadPath/evidence/photo.jpg")
            ->andReturn(true);
        File::shouldReceive('delete')->once();

        $evidence = Evidence::factory()->make(['src' => 'evidence/photo.jpg']);
        $evidence->deleteImage();
    }
}

