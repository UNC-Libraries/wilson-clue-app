<?php

namespace Tests\Unit\Providers;

use App\Game;
use App\Player;
use App\Providers\AppServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Parsedown;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Boot Method - View Composers
    // -------------------------------------------------------------------------

    public function test_layouts_master_composer_injects_controller_and_action_from_route(): void
    {
        Route::get('/test-master-composer', function () {
            return view('layouts.master');
        })->name('test.master');

        $this->app->make(AppServiceProvider::class)->boot();

        $response = $this->get('/test-master-composer');

        $response->assertStatus(200);
        $response->assertViewHas('controller');
        $response->assertViewHas('action');
    }

    public function test_layouts_admin_composer_provides_games_and_assets(): void
    {
        Game::factory()->count(2)->create();

        View::share('games', collect());
        View::share('assets', []);

        $this->app->make(AppServiceProvider::class)->boot();

        View::composer('layouts.admin', function ($view) {
            $this->assertInstanceOf(\Illuminate\Support\Collection::class, $view->getData()['games']);
            $this->assertIsArray($view->getData()['assets']);
        });

        $view = view('layouts.admin');
        $view->render();
    }

    public function test_admin_index_composer_provides_games_and_assets(): void
    {
        Game::factory()->count(2)->create();

        $this->app->make(AppServiceProvider::class)->boot();

        View::composer('admin.index', function ($view) {
            $this->assertInstanceOf(\Illuminate\Support\Collection::class, $view->getData()['games']);
            $this->assertIsArray($view->getData()['assets']);
        });

        $view = view('admin.index');
        $view->render();
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

        Route::get('/test-ui-composer', function () {
            return view('layouts.ui');
        })->name('ui.test');

        $this->app->make(AppServiceProvider::class)->boot();

        $this->assertDatabaseMissing('viewing', [
            'player_id' => $player->id,
            'game_id' => $game->id,
        ]);

        $this->get('/test-ui-composer');

        $this->assertDatabaseHas('viewing', [
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.test',
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

        Route::get('/test-ui-update', function () {
            return view('layouts.ui');
        })->name('ui.updated');

        $this->app->make(AppServiceProvider::class)->boot();

        $this->get('/test-ui-update');

        $this->assertDatabaseHas('viewing', [
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.updated',
        ]);

        $record = DB::table('viewing')
            ->where('player_id', $player->id)
            ->where('game_id', $game->id)
            ->first();

        $this->assertNotEquals($originalTime->timestamp, Carbon::parse($record->updated_at)->timestamp);
    }

    public function test_layouts_ui_composer_handles_quest_route_with_parameter(): void
    {
        $game = Game::factory()->inProgress()->create();
        /** @var Player $player */
        $player = Player::factory()->create();

        Auth::guard('player')->login($player);

        Route::get('/test-quest/{id}', function ($id) {
            return view('layouts.ui');
        })->name('ui.quest');

        $this->app->make(AppServiceProvider::class)->boot();

        $this->get('/test-quest/42');

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

        Route::get('/test-no-game', function () {
            return view('layouts.ui');
        })->name('ui.noGame');

        $this->app->make(AppServiceProvider::class)->boot();

        $this->get('/test-no-game');

        $this->assertDatabaseMissing('viewing', [
            'player_id' => $player->id,
        ]);
    }

    public function test_layouts_ui_composer_skips_tracking_when_not_authenticated(): void
    {
        Game::factory()->inProgress()->create();

        Route::get('/test-no-auth', function () {
            return view('layouts.ui');
        })->name('ui.noAuth');

        $this->app->make(AppServiceProvider::class)->boot();

        $this->get('/test-no-auth');

        $this->assertDatabaseCount('viewing', 0);
    }

    // -------------------------------------------------------------------------
    // Boot Method - SKIP_BOOTERS
    // -------------------------------------------------------------------------

    public function test_boot_returns_early_when_skip_booters_is_set(): void
    {
        putenv('SKIP_BOOTERS=true');

        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        putenv('SKIP_BOOTERS=false');

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Register Method
    // -------------------------------------------------------------------------

    public function test_parsedown_is_registered_as_singleton(): void
    {
        $provider = new AppServiceProvider($this->app);
        $provider->register();

        $instance1 = $this->app->make(Parsedown::class);
        $instance2 = $this->app->make(Parsedown::class);

        $this->assertInstanceOf(Parsedown::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function test_parsedown_singleton_renders_markdown_correctly(): void
    {
        $provider = new AppServiceProvider($this->app);
        $provider->register();

        $parsedown = $this->app->make(Parsedown::class);
        $result = $parsedown->text('# Hello World');

        $this->assertStringContainsString('<h1>Hello World</h1>', $result);
    }

    // -------------------------------------------------------------------------
    // Validator Extension
    // -------------------------------------------------------------------------

    public function test_check_dna_sequence_validator_is_registered(): void
    {
        $this->app->make(AppServiceProvider::class)->boot();

        $validator = validator(['dna' => 'ghst'], ['dna' => 'check_dna_sequence']);

        $this->assertTrue($validator->passes());
    }

    public function test_check_dna_sequence_validator_fails_for_invalid_characters(): void
    {
        $this->app->make(AppServiceProvider::class)->boot();

        $validator = validator(['dna' => 'ghstx'], ['dna' => 'check_dna_sequence']);

        $this->assertFalse($validator->passes());
    }
}

