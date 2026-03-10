<?php

namespace Tests\Unit;

use App\Alert;
use App\Game;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    
    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $alert = new Alert();

        $this->assertEquals(['message'], $alert->getFillable());
    }

    
    public function test_it_can_be_mass_assigned_a_message(): void
    {
        $alert = new Alert(['message' => 'Test alert message']);

        $this->assertEquals('Test alert message', $alert->message);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    
    public function test_it_has_a_game_belongs_to_relationship(): void
    {
        $alert = new Alert();

        $this->assertInstanceOf(BelongsTo::class, $alert->game());
    }

    
    public function test_it_belongs_to_a_game(): void
    {
        $game  = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game->id]);

        $this->assertInstanceOf(Game::class, $alert->game);
        $this->assertEquals($game->id, $alert->game->id);
    }

    
    public function game_relationship_uses_correct_foreign_key(): void
    {
        $alert = new Alert();

        $this->assertEquals('game_id', $alert->game()->getForeignKeyName());
    }

    
    public function game_relationship_uses_correct_related_model(): void
    {
        $alert = new Alert();

        $this->assertInstanceOf(Game::class, $alert->game()->getRelated());
    }

    // -------------------------------------------------------------------------
    // Database
    // -------------------------------------------------------------------------

    
    public function test_it_can_be_created_in_the_database(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create([
            'game_id' => $game->id,
            'message' => 'Test alert message',
        ]);

        $this->assertDatabaseHas('alerts', [
            'id'      => $alert->id,
            'game_id' => $game->id,
            'message' => 'Test alert message',
        ]);
    }

    
    public function test_it_can_be_deleted_from_the_database(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game->id]);

        $alert->delete();

        $this->assertDatabaseMissing('alerts', ['id' => $alert->id]);
    }

    
    public function deleting_a_game_does_not_automatically_delete_its_alerts(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game->id]);

        $game->delete();

        $this->assertDatabaseHas('alerts', ['id' => $alert->id]);
    }
}

