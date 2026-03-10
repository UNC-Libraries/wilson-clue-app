<?php

namespace Tests\Feature\Http\Controllers;

use App\Agent;
use App\Evidence;
use App\Game;
use App\Http\Controllers\WebController;
use App\Location;
use App\Suspect;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WebControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Force in-memory SQLite so RefreshDatabase does not hit external DB.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        parent::setUp();

        if (! Route::has('web.login.test')) {
            Route::middleware('web')
                ->get('/__web-login-test', [WebController::class, 'login'])
                ->name('web.login.test');
        }
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_renders_welcome_with_expected_view_data(): void
    {
        Suspect::factory()->count(2)->create();

        $activeGame = Game::factory()->create([
            'active' => true,
            'start_time' => '2026-03-15 18:30:00',
        ]);

        $archivedGame = Game::factory()->archived()->create();
        $winner = Team::factory()->create(['game_id' => $archivedGame->id, 'waitlist' => false]);
        $archivedGame->winning_team = $winner->id;
        $archivedGame->save();

        Agent::factory()->create(['retired' => false, 'web_display' => true]);
        Agent::factory()->create(['retired' => true, 'web_display' => true]);
        Agent::factory()->create(['retired' => false, 'web_display' => false]);

        DB::table('globals')->insert([
            ['key' => 'homepage', 'message' => 'Welcome message'],
            ['key' => 'special_notice', 'message' => 'Starts ||game_date|| at ||game_time||'],
            ['key' => 'registration_closed', 'message' => 'Closed after ||game_date|| ||game_time||'],
        ]);

        $response = $this->get(route('web.index'));

        $response->assertStatus(200);
        $response->assertViewIs('web.welcome');
        $response->assertViewHas('game', fn ($game) => $game->id === $activeGame->id);
        $response->assertViewHas('homepageAlert', 'Welcome message');
        $response->assertViewHas('special_notice', 'Starts Sunday, March 15th at 6:30 PM');
        $response->assertViewHas('registration_closed', 'Closed after Sunday, March 15th 6:30 PM');
        $response->assertViewHas('suspects', fn ($suspects) => $suspects->count() === 2);
        $response->assertViewHas('games', fn ($games) => $games->pluck('id')->contains($archivedGame->id));
        $response->assertViewHas('agents', function ($agents) {
            return isset($agents['active'], $agents['retired'])
                && $agents['active']->count() === 1
                && $agents['retired']->count() === 1;
        });
    }

    public function test_index_uses_latest_active_game_by_start_time(): void
    {
        $olderGame = Game::factory()->create([
            'active' => true,
            'start_time' => '2026-03-01 12:00:00',
        ]);

        $newerGame = Game::factory()->create([
            'active' => true,
            'start_time' => '2026-03-20 12:00:00',
        ]);

        $response = $this->get(route('web.index'));

        $response->assertStatus(200);
        $response->assertViewHas('game', fn ($game) => $game->id === $newerGame->id && $game->id !== $olderGame->id);
    }

    public function test_index_handles_missing_globals_and_no_active_game(): void
    {
        Suspect::factory()->create();
        $archivedGame = Game::factory()->archived()->create();
        $winner = Team::factory()->create(['game_id' => $archivedGame->id, 'waitlist' => false]);
        $archivedGame->winning_team = $winner->id;
        $archivedGame->save();

        $response = $this->get(route('web.index'));

        $response->assertStatus(200);
        $response->assertViewIs('web.welcome');
        $response->assertViewHas('game', null);
        $response->assertViewHas('homepageAlert', '');
        $response->assertViewHas('special_notice', null);
        $response->assertViewHas('registration_closed', null);
    }

    // -------------------------------------------------------------------------
    // archive
    // -------------------------------------------------------------------------

    public function test_archive_sorts_teams_by_correctness_then_score_and_sets_places(): void
    {
        $solutionSuspect = Suspect::factory()->create();
        $solutionLocation = Location::factory()->create();
        $solutionEvidence = Evidence::factory()->create();

        $otherSuspect = Suspect::factory()->create();
        $otherLocation = Location::factory()->create();
        $otherEvidence = Evidence::factory()->create();

        $game = Game::factory()->create([
            'suspect_id' => $solutionSuspect->id,
            'location_id' => $solutionLocation->id,
            'evidence_id' => $solutionEvidence->id,
        ]);

        $correctHigh = Team::factory()->create([
            'game_id' => $game->id,
            'waitlist' => false,
            'suspect_id' => $solutionSuspect->id,
            'location_id' => $solutionLocation->id,
            'evidence_id' => $solutionEvidence->id,
            'score' => 95,
        ]);

        $correctLow = Team::factory()->create([
            'game_id' => $game->id,
            'waitlist' => false,
            'suspect_id' => $solutionSuspect->id,
            'location_id' => $solutionLocation->id,
            'evidence_id' => $solutionEvidence->id,
            'score' => 80,
        ]);

        $incorrectHigh = Team::factory()->create([
            'game_id' => $game->id,
            'waitlist' => false,
            'suspect_id' => $otherSuspect->id,
            'location_id' => $otherLocation->id,
            'evidence_id' => $otherEvidence->id,
            'score' => 99,
        ]);

        $response = $this->get(route('web.archive', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('web.archive');
        $response->assertViewHas('game', fn ($archiveGame) => $archiveGame->id === $game->id);
        $response->assertViewHas('teams', fn ($teams) => $teams->pluck('id')->values()->all() === [
            $correctHigh->id,
            $correctLow->id,
            $incorrectHigh->id,
        ]);
        $response->assertViewHas('first_place', fn ($team) => $team->id === $correctHigh->id);
        $response->assertViewHas('second_place', fn ($team) => $team->id === $correctLow->id);
        $response->assertViewHas('third_place', fn ($team) => $team->id === $incorrectHigh->id);
    }

    public function test_archive_returns_404_when_game_is_missing(): void
    {
        $response = $this->get(route('web.archive', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // login
    // -------------------------------------------------------------------------

    public function test_login_returns_login_view(): void
    {
        $response = $this->get('/__web-login-test');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }
}

