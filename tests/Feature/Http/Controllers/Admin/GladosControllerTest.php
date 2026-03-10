<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Game;
use App\Location;
use App\Player;
use App\Quest;
use App\Suspect;
use App\Team;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GladosControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Force an in-memory SQLite database before the application boots so
        // that RefreshDatabase can run migrate:fresh locally without the VM's
        // MySQL server being reachable.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE']   = ':memory:';

        parent::setUp();
    }

    private function actingAsAdmin()
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // viewing
    // -------------------------------------------------------------------------

    public function test_viewing_displays_current_viewing_stats_for_game(): void
    {
        $game = Game::factory()->create();
        $player = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.index',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.evidence',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('glados.viewing');
        $response->assertViewHas('total', 2);
        $response->assertViewHas('views', function ($views) {
            return count($views) === 2;
        });
    }

    public function test_viewing_only_includes_records_updated_within_last_30_minutes(): void
    {
        $game = Game::factory()->create();
        $player = Player::factory()->create();

        // Recent viewing (within 30 minutes)
        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.index',
            'created_at' => Carbon::now()->subMinutes(10),
            'updated_at' => Carbon::now()->subMinutes(10),
        ]);

        // Old viewing (over 30 minutes ago)
        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.evidence',
            'created_at' => Carbon::now()->subMinutes(45),
            'updated_at' => Carbon::now()->subMinutes(45),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('total', 1);
        $response->assertViewHas('views', function ($views) {
            return count($views) === 1 && $views[0]['name'] === 'Index';
        });
    }

    public function test_viewing_filters_by_game_id(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();
        $player = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $gameA->id,
            'route' => 'ui.index',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $gameB->id,
            'route' => 'ui.evidence',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $gameA->id));

        $response->assertStatus(200);
        $response->assertViewHas('total', 1);
        $response->assertViewHas('views', function ($views) {
            return count($views) === 1 && $views[0]['name'] === 'Index';
        });
    }

    public function test_viewing_groups_records_by_route(): void
    {
        $game = Game::factory()->create();
        $player1 = Player::factory()->create();
        $player2 = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player1->id,
            'game_id' => $game->id,
            'route' => 'ui.index',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('viewing')->insert([
            'player_id' => $player2->id,
            'game_id' => $game->id,
            'route' => 'ui.index',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('total', 2);
        $response->assertViewHas('views', function ($views) {
            return count($views) === 1
                && $views[0]['name'] === 'Index'
                && $views[0]['count'] === 2;
        });
    }

    public function test_viewing_calculates_percentage_correctly(): void
    {
        $game = Game::factory()->create();
        $player = Player::factory()->create();

        // 3 index, 1 evidence = 75% index, 25% evidence
        for ($i = 0; $i < 3; $i++) {
            DB::table('viewing')->insert([
                'player_id' => $player->id,
                'game_id' => $game->id,
                'route' => 'ui.index',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.evidence',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('views', function ($views) {
            $indexView = collect($views)->firstWhere('name', 'Index');
            $evidenceView = collect($views)->firstWhere('name', 'Evidence');

            // floor() returns float, so use == not ===
            return $indexView['count'] === 3
                && $indexView['percent'] == 75
                && $evidenceView['count'] === 1
                && $evidenceView['percent'] == 25;
        });
    }

    public function test_viewing_formats_non_quest_route_names(): void
    {
        $game = Game::factory()->create();
        $player = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.evidence',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.geographicInvestigation',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('views', function ($views) {
            $names = collect($views)->pluck('name')->toArray();
            // Controller: ucfirst(str_replace('ui.', '', $route))
            // 'ui.geographicInvestigation' → 'GeographicInvestigation'
            return in_array('Evidence', $names)
                && in_array('GeographicInvestigation', $names);
        });
    }

    public function test_viewing_handles_quest_routes_with_suspect_and_location_names(): void
    {
        $game = Game::factory()->create();
        $suspect = Suspect::factory()->create(['name' => 'Colonel Mustard']);
        $location = Location::factory()->create(['name' => 'Library']);
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
        ]);
        $player = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => "ui.quest--{$quest->id}",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('views', function ($views) {
            return count($views) === 1
                && $views[0]['name'] === 'Colonel Mustard (Library)'
                && $views[0]['count'] === 1;
        });
    }

    public function test_viewing_handles_multiple_quest_routes(): void
    {
        $game = Game::factory()->create();
        $suspect1 = Suspect::factory()->create(['name' => 'Miss Scarlet']);
        $suspect2 = Suspect::factory()->create(['name' => 'Professor Plum']);
        $location1 = Location::factory()->create(['name' => 'Ballroom']);
        $location2 = Location::factory()->create(['name' => 'Study']);

        $quest1 = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect1->id,
            'location_id' => $location1->id,
        ]);

        $quest2 = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect2->id,
            'location_id' => $location2->id,
        ]);

        $player = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => "ui.quest--{$quest1->id}",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => "ui.quest--{$quest2->id}",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('views', function ($views) {
            $names = collect($views)->pluck('name')->toArray();
            return in_array('Miss Scarlet (Ballroom)', $names)
                && in_array('Professor Plum (Study)', $names);
        });
    }

    public function test_viewing_returns_empty_stats_when_no_viewing_records_exist(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('total', 0);
        $response->assertViewHas('views', function ($views) {
            return count($views) === 0;
        });
    }

    public function test_viewing_returns_empty_stats_when_all_records_are_stale(): void
    {
        $game = Game::factory()->create();
        $player = Player::factory()->create();

        DB::table('viewing')->insert([
            'player_id' => $player->id,
            'game_id' => $game->id,
            'route' => 'ui.index',
            'created_at' => Carbon::now()->subHours(2),
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.viewing', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('total', 0);
        $response->assertViewHas('views', []);
    }

    // -------------------------------------------------------------------------
    // status
    // -------------------------------------------------------------------------

    public function test_status_displays_teams_and_quests_for_game(): void
    {
        $game = Game::factory()->create();
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('glados.status');
        $response->assertViewHas('teams', function ($teams) use ($team) {
            return $teams->pluck('id')->contains($team->id);
        });
        $response->assertViewHas('quests', function ($quests) use ($quest) {
            return $quests->pluck('id')->contains($quest->id);
        });
    }

    public function test_status_only_includes_registered_teams(): void
    {
        $game = Game::factory()->create();
        $registeredTeam = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $waitlistTeam = Team::factory()->create(['game_id' => $game->id, 'waitlist' => true]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('teams', function ($teams) use ($registeredTeam, $waitlistTeam) {
            return $teams->pluck('id')->contains($registeredTeam->id)
                && !$teams->pluck('id')->contains($waitlistTeam->id);
        });
    }

    public function test_status_filters_teams_by_game_id(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();
        $teamA = Team::factory()->create(['game_id' => $gameA->id, 'waitlist' => false]);
        $teamB = Team::factory()->create(['game_id' => $gameB->id, 'waitlist' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $gameA->id));

        $response->assertStatus(200);
        $response->assertViewHas('teams', function ($teams) use ($teamA, $teamB) {
            return $teams->pluck('id')->contains($teamA->id)
                && !$teams->pluck('id')->contains($teamB->id);
        });
    }

    public function test_status_filters_quests_by_game_id(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();
        $suspectA = Suspect::factory()->create();
        $suspectB = Suspect::factory()->create();
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $teamA = Team::factory()->create(['game_id' => $gameA->id, 'waitlist' => false]);
        $questA = Quest::factory()->create([
            'game_id' => $gameA->id,
            'suspect_id' => $suspectA->id,
            'location_id' => $locationA->id,
        ]);
        $questB = Quest::factory()->create([
            'game_id' => $gameB->id,
            'suspect_id' => $suspectB->id,
            'location_id' => $locationB->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $gameA->id));

        $response->assertStatus(200);
        $response->assertViewHas('quests', function ($quests) use ($questA, $questB) {
            return $quests->pluck('id')->contains($questA->id)
                && !$quests->pluck('id')->contains($questB->id);
        });
    }

    public function test_status_eager_loads_quest_relationships(): void
    {
        $game = Game::factory()->create();
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
        ]);
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);

        $quest->completedBy()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('quests', function ($quests) use ($suspect, $team) {
            $quest = $quests->first();
            return $quest->relationLoaded('completedBy')
                && $quest->relationLoaded('suspect')
                && $quest->suspect->id === $suspect->id
                && $quest->completedBy->pluck('id')->contains($team->id);
        });
    }

    public function test_status_returns_empty_collections_when_no_teams_or_quests_exist(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('teams', function ($teams) {
            return $teams->isEmpty();
        });
        $response->assertViewHas('quests', function ($quests) {
            return $quests->isEmpty();
        });
    }

    public function test_status_handles_teams_without_completed_quests(): void
    {
        $game = Game::factory()->create();
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('teams', function ($teams) use ($team) {
            return $teams->count() === 1;
        });
        $response->assertViewHas('quests', function ($quests) use ($quest) {
            $loadedQuest = $quests->first();
            return $loadedQuest->id === $quest->id
                && $loadedQuest->completedBy->isEmpty();
        });
    }

    public function test_status_includes_multiple_teams_and_quests(): void
    {
        $game = Game::factory()->create();
        $suspect1 = Suspect::factory()->create();
        $suspect2 = Suspect::factory()->create();
        $location1 = Location::factory()->create();
        $location2 = Location::factory()->create();

        $team1 = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $team2 = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);

        $quest1 = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect1->id,
            'location_id' => $location1->id,
        ]);
        $quest2 = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect2->id,
            'location_id' => $location2->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.glados.status', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('teams', function ($teams) {
            return $teams->count() === 2;
        });
        $response->assertViewHas('quests', function ($quests) {
            return $quests->count() === 2;
        });
    }
}

