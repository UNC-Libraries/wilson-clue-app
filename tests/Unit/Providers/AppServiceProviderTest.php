<?php

namespace Tests\Unit\Providers;

use App\Game;
use App\Player;
use App\Providers\AppServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): AppServiceProvider
    {
        return new AppServiceProvider($this->app);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Some test runs export SKIP_BOOTERS=true for migrations; ensure
        // this class exercises the real provider boot logic.
        putenv('SKIP_BOOTERS');
        unset($_ENV['SKIP_BOOTERS']);
        unset($_SERVER['SKIP_BOOTERS']);

        $this->provider()->boot();
    }

    // -------------------------------------------------------------------------
    // Boot Method - View Composers
    // -------------------------------------------------------------------------

    public function test_layouts_admin_composer_provides_games_and_assets(): void
    {
        Game::factory()->create(['name' => 'Game One']);
        Game::factory()->create(['name' => 'Game Two']);

        $html = View::make('layouts.admin')->render();

        $this->assertStringContainsString('Game One', $html);
        $this->assertStringContainsString('Game Two', $html);
    }

    public function test_admin_index_composer_provides_games_and_assets(): void
    {
        Game::factory()->create(['name' => 'Admin Game One']);
        Game::factory()->create(['name' => 'Admin Game Two']);

        $html = View::make('admin.index')->render();

        $this->assertStringContainsString('Admin Game One', $html);
        $this->assertStringContainsString('Admin Game Two', $html);
    }

    // -------------------------------------------------------------------------
    // Boot Method - Viewing Table Tracking
    // -------------------------------------------------------------------------

    public function test_layouts_ui_composer_creates_viewing_record_for_new_session(): void
    {
        $game = Game::factory()->inProgress()->create();
        /** @var Player $player */
        $player = Player::factory()->create();

        Auth::guard('player')->login($player);

        $route = \Mockery::mock();
        $route->shouldReceive('getName')->andReturn('ui.index');
        $route->shouldReceive('parameter')->with('id')->andReturn(null);
        Route::shouldReceive('current')->andReturn($route);

        $this->assertDatabaseMissing('viewing', [
            'player_id' => $player->id,
            'game_id' => $game->id,
        ]);

        View::make('layouts.ui')->render();

        $this->assertDatabaseHas('viewing', [
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.index',
        ]);
    }

    public function test_layouts_ui_composer_updates_existing_viewing_record(): void
    {
        $game = Game::factory()->inProgress()->create();
        /** @var Player $player */
        $player = Player::factory()->create();

        $originalTime = Carbon::now()->subMinutes(5);

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.old',
            'created_at' => $originalTime,
            'updated_at' => $originalTime,
        ]);

        Auth::guard('player')->login($player);

        $route = \Mockery::mock();
        $route->shouldReceive('getName')->andReturn('ui.index');
        $route->shouldReceive('parameter')->with('id')->andReturn(null);
        Route::shouldReceive('current')->andReturn($route);

        View::make('layouts.ui')->render();

        $record = DB::table('viewing')
            ->where('player_id', $player->id)
            ->where('game_id', $game->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertNotEquals('ui.old', $record->route);
    }

    public function test_layouts_ui_composer_handles_quest_route_with_parameter(): void
    {
        $game = Game::factory()->inProgress()->create();
        /** @var Player $player */
        $player = Player::factory()->create();

        Auth::guard('player')->login($player);

        $route = \Mockery::mock();
        $route->shouldReceive('getName')->andReturn('ui.quest');
        $route->shouldReceive('parameter')->with('id')->andReturn(42);
        Route::shouldReceive('current')->andReturn($route);

        View::make('layouts.ui')->render();

        $this->assertDatabaseHas('viewing', [
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.quest--42',
        ]);
    }

    public function test_layouts_ui_composer_skips_tracking_when_no_game(): void
    {
        /** @var Player $player */
        $player = Player::factory()->create();

        Auth::guard('player')->login($player);

        $route = \Mockery::mock();
        $route->shouldReceive('getName')->andReturn('ui.index');
        $route->shouldReceive('parameter')->with('id')->andReturn(null);
        Route::shouldReceive('current')->andReturn($route);

        View::make('layouts.ui')->render();

        $this->assertDatabaseMissing('viewing', [
            'player_id' => $player->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Validator Extension
    // -------------------------------------------------------------------------

    public function test_check_dna_sequence_validator_is_registered(): void
    {
        $validator = validator(['dna' => 'ghst'], ['dna' => 'check_dna_sequence']);

        $this->assertTrue($validator->passes());
    }

    public function test_check_dna_sequence_validator_currently_allows_extra_characters_when_core_letters_exist(): void
    {
        $validator = validator(['dna' => 'ghstx'], ['dna' => 'check_dna_sequence']);

        $this->assertTrue($validator->passes());
    }
}

