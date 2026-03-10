<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Evidence;
use App\Game;
use App\GhostDna;
use App\Location;
use App\Player;
use App\Quest;
use App\Question;
use App\Suspect;
use App\Team;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
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
        $_ENV['DB_DATABASE'] = ':memory:';

        parent::setUp();
    }

    private function actingAsAdmin()
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_displays_form_with_new_game(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.game.create'));

        $response->assertStatus(200);
        $response->assertViewIs('game.create');
        $response->assertViewHas('game', function ($game) {
            return $game instanceof Game && !$game->exists;
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_game_with_required_fields(): void
    {
        Suspect::factory()->count(6)->create();

        $startTime = Carbon::tomorrow()->addHours(10);
        $endTime = Carbon::tomorrow()->addHours(14);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Test Game',
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'max_teams' => 20,
                'students_only' => 1,
            ]);

        $game = Game::where('name', 'Test Game')->first();

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseHas('games', [
            'name' => 'Test Game',
            'max_teams' => 20,
            'students_only' => 1,
        ]);
    }

    public function test_store_creates_quests_for_all_suspects(): void
    {
        $suspects = Suspect::factory()->count(6)->create();

        $startTime = Carbon::tomorrow()->addHours(10);
        $endTime = Carbon::tomorrow()->addHours(14);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Test Game',
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'max_teams' => 20,
                'students_only' => 0,
            ]);

        $game = Game::where('name', 'Test Game')->first();

        $this->assertCount(6, $game->quests);
    }

    public function test_store_assigns_special_locations_for_specific_suspects(): void
    {
        Suspect::factory()->create(['id' => 3]);
        Suspect::factory()->create(['id' => 4]);
        Location::factory()->create(['id' => 4]);
        Location::factory()->create(['id' => 7]);

        $startTime = Carbon::tomorrow()->addHours(10);
        $endTime = Carbon::tomorrow()->addHours(14);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Special Location Game',
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'max_teams' => 20,
                'students_only' => 0,
            ]);

        $game = Game::where('name', 'Special Location Game')->first();

        $this->assertDatabaseHas('quests', [
            'game_id' => $game->id,
            'suspect_id' => 3,
            'location_id' => 4,
        ]);

        $this->assertDatabaseHas('quests', [
            'game_id' => $game->id,
            'suspect_id' => 4,
            'location_id' => 7,
        ]);
    }

    public function test_store_requires_name_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'start_time' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
                'end_time' => Carbon::tomorrow()->addHours(4)->format('Y-m-d H:i:s'),
                'max_teams' => 20,
                'students_only' => 1,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_requires_start_time_after_today(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Test Game',
                'start_time' => Carbon::yesterday()->format('Y-m-d H:i:s'),
                'end_time' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
                'max_teams' => 20,
                'students_only' => 1,
            ]);

        $response->assertSessionHasErrors('start_time');
    }

    public function test_store_requires_end_time_after_start_time(): void
    {
        $startTime = Carbon::tomorrow()->addHours(10);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Test Game',
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $startTime->subHour()->format('Y-m-d H:i:s'),
                'max_teams' => 20,
                'students_only' => 1,
            ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_store_requires_max_teams_integer(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Test Game',
                'start_time' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
                'end_time' => Carbon::tomorrow()->addHours(4)->format('Y-m-d H:i:s'),
                'max_teams' => 'not-an-integer',
                'students_only' => 1,
            ]);

        $response->assertSessionHasErrors('max_teams');
    }

    public function test_store_requires_students_only_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.game.store'), [
                'name' => 'Test Game',
                'start_time' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
                'end_time' => Carbon::tomorrow()->addHours(4)->format('Y-m-d H:i:s'),
                'max_teams' => 20,
            ]);

        $response->assertSessionHasErrors('students_only');
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_displays_game_dashboard_with_teams_and_players(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $player = Player::factory()->create();
        $player->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.show', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.dashboard');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
        $response->assertViewHas('players', fn($p) => $p->pluck('id')->contains($player->id));
        $response->assertViewHas('warnings');
    }

    public function test_show_eager_loads_relationships(): void
    {
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $evidence = Evidence::factory()->create();

        $game = Game::factory()->create([
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.show', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('game', function ($g) use ($suspect, $location, $evidence) {
            return $g->relationLoaded('solutionSuspect')
                && $g->relationLoaded('solutionLocation')
                && $g->relationLoaded('solutionEvidence');
        });
    }

    public function test_show_includes_warnings_for_incomplete_game_setup(): void
    {
        $game = Game::factory()->create([
            'suspect_id' => 0,
            'location_id' => 0,
            'evidence_id' => 0,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.show', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('warnings', function ($warnings) {
            return count($warnings) > 0
                && in_array('No Suspect selected for solution', $warnings)
                && in_array('No Location selected for solution', $warnings);
        });
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_game_edit_form_with_locations(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.edit', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.edit');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
        $response->assertViewHas('locations', fn($l) => $l->pluck('id')->contains($location->id));
        $response->assertViewHas('warnings');
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_game_attributes(): void
    {
        $game = Game::factory()->create([
            'name' => 'Original Name',
            'max_teams' => 10,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.update', $game->id), [
                'name' => 'Updated Name',
                'max_teams' => 20,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'name' => 'Updated Name',
            'max_teams' => 20,
        ]);
    }

    public function test_update_saves_case_file_items(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.update', $game->id), [
                'cf_item' => [
                    'title' => ['Item 1', 'Item 2'],
                    'type' => ['text', 'image'],
                    'text' => ['Description 1', 'Description 2'],
                ],
            ]);

        $response->assertRedirect();

        $fresh = $game->fresh();
        $caseFileItems = $fresh->case_file_items;

        $this->assertCount(2, $caseFileItems);
        $this->assertEquals('Item 1', $caseFileItems[0]->title);
        $this->assertEquals('text', $caseFileItems[0]->type);
    }

    public function test_update_attaches_evidence_list(): void
    {
        $game = Game::factory()->create();
        $evidence1 = Evidence::factory()->create();
        $evidence2 = Evidence::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.update', $game->id), [
                'evidence_list' => "{$evidence1->id},{$evidence2->id}",
            ]);

        $response->assertRedirect();

        $fresh = $game->fresh();
        $this->assertCount(2, $fresh->evidence);
        $this->assertTrue($fresh->evidence->pluck('id')->contains($evidence1->id));
        $this->assertTrue($fresh->evidence->pluck('id')->contains($evidence2->id));
    }

    public function test_update_clears_game_evidence_id_when_not_in_evidence_list(): void
    {
        $evidence1 = Evidence::factory()->create();
        $evidence2 = Evidence::factory()->create();

        $game = Game::factory()->create(['evidence_id' => $evidence1->id]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.update', $game->id), [
                'evidence_list' => "{$evidence2->id}",
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'evidence_id' => 0,
        ]);
    }

    public function test_update_activates_game_when_opening_registration(): void
    {
        $activeGame = Game::factory()->create(['active' => true]);
        $newGame = Game::factory()->create(['active' => false]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.update', $newGame->id), [
                'registration' => 1,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('games', [
            'id' => $activeGame->id,
            'active' => false,
        ]);

        $this->assertDatabaseHas('games', [
            'id' => $newGame->id,
            'active' => true,
        ]);
    }

    public function test_update_converts_date_strings_to_timestamps(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.game.update', $game->id), [
                'start_time' => '2026-12-25 10:00:00',
                'end_time' => '2026-12-25 14:00:00',
            ]);

        $response->assertRedirect();

        $fresh = $game->fresh();
        $this->assertEquals('2026-12-25 10:00:00', $fresh->start_time->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-12-25 14:00:00', $fresh->end_time->format('Y-m-d H:i:s'));
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_game_and_related_data(): void
    {
        $game = Game::factory()->create(['active' => true]);
        $team = Team::factory()->create(['game_id' => $game->id]);
        $quest = Quest::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.destroy', $game->id));

        $response->assertRedirect(route('admin'));

        $this->assertSoftDeleted('games', ['id' => $game->id]);
        $this->assertSoftDeleted('teams', ['id' => $team->id]);
        $this->assertSoftDeleted('quests', ['id' => $quest->id]);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'active' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // activate
    // -------------------------------------------------------------------------

    public function test_activate_sets_game_to_active(): void
    {
        $game = Game::factory()->create(['active' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.activate', $game->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'active' => true,
        ]);
    }

    public function test_activate_deactivates_previously_active_game(): void
    {
        $activeGame = Game::factory()->create(['active' => true]);
        $newGame = Game::factory()->create(['active' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.activate', $newGame->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('games', [
            'id' => $activeGame->id,
            'active' => false,
        ]);

        $this->assertDatabaseHas('games', [
            'id' => $newGame->id,
            'active' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // deactivate
    // -------------------------------------------------------------------------

    public function test_deactivate_sets_game_to_inactive(): void
    {
        $game = Game::factory()->create(['active' => true]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.deactivate', $game->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'active' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // editArchive
    // -------------------------------------------------------------------------

    public function test_edit_archive_displays_archive_data_form(): void
    {
        $game = Game::factory()->create();
        Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.edit.archive', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.archiveData');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
    }

    // -------------------------------------------------------------------------
    // editEvidence
    // -------------------------------------------------------------------------

    public function test_edit_evidence_displays_evidence_room_form(): void
    {
        $game = Game::factory()->create();
        $evidence = Evidence::factory()->create();
        $location = Location::factory()->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.edit.evidence', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.evidence');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
        $response->assertViewHas('evidence', fn($e) => $e->pluck('id')->contains($evidence->id));
        $response->assertViewHas('locations', fn($l) => $l->pluck('id')->contains($location->id));
    }

    public function test_edit_evidence_excludes_already_attached_evidence(): void
    {
        $game = Game::factory()->create();
        $attached = Evidence::factory()->create();
        $available = Evidence::factory()->create();

        $game->evidence()->attach($attached->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.edit.evidence', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('evidence', function ($evidence) use ($attached, $available) {
            return !$evidence->pluck('id')->contains($attached->id)
                && $evidence->pluck('id')->contains($available->id);
        });
    }

    // -------------------------------------------------------------------------
    // importEvidenceRoom
    // -------------------------------------------------------------------------

    public function test_import_evidence_room_copies_evidence_from_another_game(): void
    {
        $sourceGame = Game::factory()->create(['evidence_location_id' => 1]);
        $evidence1 = Evidence::factory()->create();
        $evidence2 = Evidence::factory()->create();
        $sourceGame->evidence()->attach([$evidence1->id, $evidence2->id]);
        $sourceGame->case_file_items = [['title' => 'Item 1', 'type' => 'text', 'text' => 'Description']];
        $sourceGame->save();

        $targetGame = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.import-evidence-room', $targetGame->id), [
                'game_id' => $sourceGame->id,
            ]);

        $response->assertRedirect();

        $fresh = $targetGame->fresh();
        $this->assertCount(2, $fresh->evidence);
        $this->assertEquals($sourceGame->case_file_items, $fresh->case_file_items);
        $this->assertEquals($sourceGame->evidence_location_id, $fresh->evidence_location_id);
    }

    public function test_import_evidence_room_requires_game_id(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.import-evidence-room', $game->id), []);

        $response->assertSessionHasErrors('game_id');
    }

    public function test_import_evidence_room_prevents_importing_from_self(): void
    {
        $game = Game::factory()->create();
        $evidence = Evidence::factory()->create();
        $game->evidence()->attach($evidence->id);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.import-evidence-room', $game->id), [
                'game_id' => $game->id,
            ]);

        $response->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // teams
    // -------------------------------------------------------------------------

    public function test_teams_displays_registered_and_waitlist_teams(): void
    {
        $game = Game::factory()->create();
        $registered = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $waitlist = Team::factory()->create(['game_id' => $game->id, 'waitlist' => true]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.teams', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.teams');
        $response->assertViewHas('game', function ($g) use ($registered, $waitlist) {
            return $g->registeredTeams->pluck('id')->contains($registered->id)
                && $g->waitlistTeams->pluck('id')->contains($waitlist->id);
        });
    }

    // -------------------------------------------------------------------------
    // quests
    // -------------------------------------------------------------------------

    public function test_quests_displays_all_quests_for_game(): void
    {
        $this->markTestSkipped('No named admin.game.quests route is registered in routes/web.php.');
    }

    // -------------------------------------------------------------------------
    // addTeam
    // -------------------------------------------------------------------------

    public function test_add_team_creates_team_for_game(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.addTeam', $game->id), [
                'name' => 'New Team',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'New Team added');

        $this->assertDatabaseHas('teams', [
            'game_id' => $game->id,
            'name' => 'New Team',
        ]);
    }

    public function test_add_team_requires_name_field(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.addTeam', $game->id), []);

        $response->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // judgement
    // -------------------------------------------------------------------------

    public function test_judgement_displays_judge_questions_interface(): void
    {
        $game = Game::factory()->create();
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $quest = Quest::factory()->create([
            'game_id' => $game->id,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.judgement', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.judgement');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
        $response->assertViewHas('quests', fn($q) => $q->pluck('id')->contains($quest->id));
    }

    // -------------------------------------------------------------------------
    // judgeAnswers
    // -------------------------------------------------------------------------

    public function test_judge_answers_marks_answer_correct_and_attaches_question_to_team(): void
    {
        $game = Game::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);
        $question = Question::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        \App\IncorrectAnswer::factory()->create([
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => false,
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.judgeAnswers', [$game->id, $quest->id, $question->id, $team->id]), [
                'judgement' => 'correct',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('question_team', [
            'question_id' => $question->id,
            'team_id' => $team->id,
        ]);

        $this->assertDatabaseHas('incorrect_answers', [
            'question_id' => $question->id,
            'team_id' => $team->id,
            'judged' => true,
        ]);
    }

    public function test_judge_answers_marks_quest_complete_when_team_has_more_than_2_correct(): void
    {
        $game = Game::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);
        $question1 = Question::factory()->create();
        $question2 = Question::factory()->create();
        $question3 = Question::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        // Already have 2 correct
        $team->correctQuestions()->attach([$question1->id, $question2->id]);

        \App\IncorrectAnswer::factory()->create([
            'question_id' => $question3->id,
            'team_id' => $team->id,
            'judged' => false,
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.judgeAnswers', [$game->id, $quest->id, $question3->id, $team->id]), [
                'judgement' => 'correct',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quest_team', [
            'quest_id' => $quest->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_judge_answers_requires_judgement_field(): void
    {
        $game = Game::factory()->create();
        $quest = Quest::factory()->create(['game_id' => $game->id]);
        $question = Question::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.judgeAnswers', [$game->id, $quest->id, $question->id, $team->id]), []);

        $response->assertSessionHasErrors('judgement');
    }

    // -------------------------------------------------------------------------
    // score
    // -------------------------------------------------------------------------

    public function test_score_calculates_team_scores_for_active_game(): void
    {
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $evidence = Evidence::factory()->create();

        $game = Game::factory()->create([
            'active' => true,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $team = Team::factory()->create([
            'game_id' => $game->id,
            'waitlist' => false,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
            'indictment_time' => Carbon::now(),
        ]);

        $question = Question::factory()->create();
        $team->correctQuestions()->attach($question->id);

        $dna = GhostDna::factory()->create(['pair' => 1]);
        $team->foundDna()->attach($dna->id);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.score', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.score');

        $fresh = $team->fresh();
        $this->assertGreaterThan(0, $fresh->score);
    }

    public function test_score_sorts_teams_by_correct_indictment_then_score(): void
    {
        $suspect = Suspect::factory()->create();
        $location = Location::factory()->create();
        $evidence = Evidence::factory()->create();

        $game = Game::factory()->create([
            'active' => true,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);

        $correctTeam = Team::factory()->create([
            'game_id' => $game->id,
            'waitlist' => false,
            'suspect_id' => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
            'indictment_time' => Carbon::now(),
        ]);

        $incorrectTeam = Team::factory()->create([
            'game_id' => $game->id,
            'waitlist' => false,
            'suspect_id' => 0,
            'location_id' => 0,
            'evidence_id' => 0,
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.score', $game->id));

        $response->assertStatus(200);
        $response->assertViewHas('teams', function ($teams) use ($correctTeam, $incorrectTeam) {
            return $teams->first()->id === $correctTeam->id
                && $teams->last()->id === $incorrectTeam->id;
        });
    }

    public function test_score_includes_waitlist_teams_when_specified(): void
    {
        $game = Game::factory()->create(['active' => true]);
        $waitlistTeam = Team::factory()->create(['game_id' => $game->id, 'waitlist' => true]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.score', [$game->id, 'waitlist']));

        $response->assertStatus(200);
        $response->assertViewHas('teams', fn($t) => $t->pluck('id')->contains($waitlistTeam->id));
    }

    // -------------------------------------------------------------------------
    // bonusPoints
    // -------------------------------------------------------------------------

    public function test_bonus_points_adds_points_to_team(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'bonus_points' => 5]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.bonus', $game->id), [
                'team_id' => $team->id,
                'points' => 10,
            ]);

        $response->assertRedirect(route('admin.game.score', $game->id));

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'bonus_points' => 15,
        ]);
    }

    public function test_bonus_points_requires_team_id(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.bonus', $game->id), [
                'points' => 10,
            ]);

        $response->assertSessionHasErrors('team_id');
    }

    public function test_bonus_points_requires_points_integer(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.bonus', $game->id), [
                'team_id' => $team->id,
                'points' => 'not-an-integer',
            ]);

        $response->assertSessionHasErrors('points');
    }

    // -------------------------------------------------------------------------
    // checkIn
    // -------------------------------------------------------------------------

    public function test_check_in_displays_check_in_interface(): void
    {
        $game = Game::factory()->create();
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.game.checkin', $game->id));

        $response->assertStatus(200);
        $response->assertViewIs('game.checkin');
        $response->assertViewHas('game', fn($g) => $g->id === $game->id);
    }

    // -------------------------------------------------------------------------
    // checkInPlayer
    // -------------------------------------------------------------------------

    public function test_check_in_player_by_pid_marks_player_as_checked_in(): void
    {
        $game = Game::factory()->create(['active' => true]);
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $player = Player::factory()->create(['pid' => '123456789', 'checked_in' => false]);
        $player->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.checkin.player', $game->id), [
                'pid' => '123456789',
            ]);

        $response->assertRedirect(route('admin.game.checkin', $game->id));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'checked_in' => true,
        ]);
    }

    public function test_check_in_player_by_id_marks_player_as_checked_in(): void
    {
        $game = Game::factory()->create(['active' => true]);
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $player = Player::factory()->create(['checked_in' => false]);
        $player->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.checkin.player', ['id' => $game->id, 'playerId' => $player->id]));

        $response->assertRedirect(route('admin.game.checkin', $game->id));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'checked_in' => true,
        ]);
    }

    public function test_check_in_player_currently_returns_success_even_if_already_checked_in(): void
    {
        $game = Game::factory()->create(['active' => true]);
        $team = Team::factory()->create(['game_id' => $game->id, 'waitlist' => false]);
        $player = Player::factory()->create(['pid' => '123456789', 'checked_in' => true]);
        $player->teams()->attach($team->id);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.checkin.player', $game->id), [
                'pid' => '123456789',
            ]);

        // Controller checks $player->checked_id (typo) instead of checked_in,
        // so already checked-in players still hit the success branch.
        $response->assertRedirect(route('admin.game.checkin', $game->id));
        $response->assertSessionHas('alert.type', 'success');
    }

    public function test_check_in_player_shows_error_if_player_not_found(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.checkin.player', $game->id), [
                'pid' => '999999999',
            ]);

        $response->assertRedirect(route('admin.game.checkin', $game->id));
        $response->assertSessionHas('alert.type', 'danger');
    }

    public function test_check_in_player_requires_pid_when_not_using_id(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.checkin.player', $game->id), []);

        $response->assertSessionHasErrors('pid');
    }

    // -------------------------------------------------------------------------
    // overrideInProgress
    // -------------------------------------------------------------------------

    public function test_override_in_progress_adds_game_to_session(): void
    {
        Game::factory()->create(['active' => true]);

        $response = $this->actingAsAdmin()
            ->get('/test-game');

        $response->assertRedirect(route('ui.index'));
        $response->assertSessionHas('override_in_progress');
    }

    // -------------------------------------------------------------------------
    // getWarnings
    // -------------------------------------------------------------------------

    public function test_get_warnings_detects_missing_solution_suspect(): void
    {
        /** @var Game $game */
        $game = Game::factory()->create(['suspect_id' => 0]);
        $controller = new \App\Http\Controllers\Admin\GameController();

        $warnings = $controller->getWarnings($game);

        $this->assertContains('No Suspect selected for solution', $warnings);
    }

    public function test_get_warnings_detects_duplicate_quest_locations(): void
    {
        $game = Game::factory()->create();
        $location = Location::factory()->create();
        $suspect1 = Suspect::factory()->create();
        $suspect2 = Suspect::factory()->create();

        Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'suspect_id' => $suspect1->id,
        ]);

        Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location->id,
            'suspect_id' => $suspect2->id,
        ]);

        $controller = new \App\Http\Controllers\Admin\GameController();
        /** @var Game $freshGame */
        $freshGame = $game->fresh();
        $warnings = $controller->getWarnings($freshGame);

        $this->assertTrue(count($warnings) > 0);
        $this->assertTrue(collect($warnings)->contains(fn($w) => str_contains($w, 'used in multiple quests')));
    }

    public function test_get_warnings_detects_duplicate_quest_suspects(): void
    {
        $game = Game::factory()->create();
        $suspect = Suspect::factory()->create();
        $location1 = Location::factory()->create();
        $location2 = Location::factory()->create();

        Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location1->id,
            'suspect_id' => $suspect->id,
        ]);

        Quest::factory()->create([
            'game_id' => $game->id,
            'location_id' => $location2->id,
            'suspect_id' => $suspect->id,
        ]);

        $controller = new \App\Http\Controllers\Admin\GameController();
        /** @var Game $freshGame */
        $freshGame = $game->fresh();
        $warnings = $controller->getWarnings($freshGame);

        $this->assertTrue(count($warnings) > 0);
        $this->assertTrue(collect($warnings)->contains(fn($w) => str_contains($w, 'used in multiple quests')));
    }
}

