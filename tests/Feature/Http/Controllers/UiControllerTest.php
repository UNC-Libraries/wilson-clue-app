<?php

namespace Tests\Feature\Http\Controllers;

use App\Alert;
use App\Evidence;
use App\Game;
use App\GhostDna;
use App\Location;
use App\MinigameImage;
use App\Player;
use App\Quest;
use App\Question;
use App\Suspect;
use App\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiControllerTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;
    private Team $team;
    private Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->game   = Game::factory()->create();
        $this->team   = Team::factory()->create(['game_id' => $this->game->id]);
        $this->player = Player::factory()->create();
        $this->team->players()->attach($this->player);
    }

    private function sessionData(): array
    {
        return [
            'gameId' => $this->game->id,
            'teamId' => $this->team->id,
        ];
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    
    public function test_index_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.index');
        $response->assertViewHasAll(['game', 'team', 'progress', 'dnaCount']);
    }

    
    public function test_index_calculates_progress_for_question_quests(): void
    {
        $quest    = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'question']);
        $question = Question::factory()->create(['quest_id' => $quest->id]);
        $question->completedBy()->attach($this->team->id);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.index'));

        $response->assertStatus(200);
        $progress = $response->viewData('progress');
        $this->assertArrayHasKey($quest->id, $progress);
        $this->assertEquals('1 of 1', $progress[$quest->id]['progressMessage']);
        $this->assertEquals(100, $progress[$quest->id]['percentComplete']);
    }

    
    public function test_index_calculates_progress_for_minigame_quests(): void
    {
        $quest = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);
        $this->team->completedQuests()->attach($quest->id);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.index'));

        $progress = $response->viewData('progress');
        $this->assertEquals('Complete', $progress[$quest->id]['progressMessage']);
        $this->assertEquals('100', $progress[$quest->id]['percentComplete']);
    }

    
    public function test_index_overrides_game_times_when_session_flag_set(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession(array_merge($this->sessionData(), ['override_in_progress' => true]))
            ->get(route('ui.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // info
    // -------------------------------------------------------------------------

    
    public function test_info_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.info'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.info');
    }

    // -------------------------------------------------------------------------
    // map
    // -------------------------------------------------------------------------

    
    public function test_map_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.map'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.map');
        $response->assertViewHasAll(['floors', 'game']);
    }

    
    public function test_map_groups_locations_by_floor(): void
    {
        $location = Location::factory()->create(['floor' => 2]);
        Quest::factory()->create(['game_id' => $this->game->id, 'location_id' => $location->id]);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.map'));

        $floors = $response->viewData('floors');
        $this->assertArrayHasKey(2, $floors->toArray());
    }

    // -------------------------------------------------------------------------
    // evidence
    // -------------------------------------------------------------------------

    
    public function test_evidence_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.evidence'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.evidence');
        $response->assertViewHasAll(['game', 'team']);
    }

    // -------------------------------------------------------------------------
    // geographicInvestigation
    // -------------------------------------------------------------------------

    
    public function test_geographic_investigation_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.geographicInvestigation'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.geographic_investigation');
        $response->assertViewHas('game');
    }

    // -------------------------------------------------------------------------
    // quest
    // -------------------------------------------------------------------------

    
    public function test_quest_returns_200_for_question_type(): void
    {
        $quest = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'question']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.quest', $quest->id));

        $response->assertStatus(200);
        $response->assertViewIs('ui.quest');
        $response->assertViewHasAll(['quest', 'team']);
    }

    
    public function test_quest_returns_200_for_minigame_type(): void
    {
        $quest = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.quest', $quest->id));

        $response->assertStatus(200);
        $response->assertViewIs('ui.quest');
    }

    
    public function test_quest_returns_404_for_invalid_id(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.quest', 9999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // indictment
    // -------------------------------------------------------------------------

    
    public function test_indictment_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.indictment'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.indictment');
        $response->assertViewHasAll(['team', 'game', 'warnings']);
    }

    
    public function test_indictment_adds_warning_for_incomplete_quest(): void
    {
        Quest::factory()->create(['game_id' => $this->game->id]);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.indictment'));

        $warnings = $response->viewData('warnings');
        $this->assertNotEmpty($warnings);
        $this->assertEquals('quest', $warnings[0]['type']);
    }

    // -------------------------------------------------------------------------
    // setIndictment
    // -------------------------------------------------------------------------

    
    public function test_set_indictment_validates_required_fields(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->post(route('ui.setIndictment'), []);

        $response->assertSessionHasErrors(['suspect', 'location', 'evidence']);
    }

    
    public function test_set_indictment_saves_and_redirects_to_index(): void
    {
        $suspect  = Suspect::factory()->create();
        $location = Location::factory()->create();
        $evidence = Evidence::factory()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->post(route('ui.setIndictment'), [
                'suspect'  => $suspect->id,
                'location' => $location->id,
                'evidence' => $evidence->id,
            ]);

        $response->assertRedirect(route('ui.index'));
        $this->assertDatabaseHas('teams', [
            'id'          => $this->team->id,
            'suspect_id'  => $suspect->id,
            'location_id' => $location->id,
            'evidence_id' => $evidence->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // attemptQuestion
    // -------------------------------------------------------------------------

    
    public function test_attempt_question_validates_attempt_field(): void
    {
        $question = Question::factory()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptQuestion', $question->id), []);

        $response->assertStatus(422);
    }

    
    public function test_attempt_question_returns_correct_true_on_matching_answer(): void
    {
        $question = Question::factory()->create();
        $question->answers()->create(['text' => 'correctanswer']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptQuestion', $question->id), ['attempt' => 'correctanswer']);

        $response->assertJson(['correct' => true]);
    }

    
    public function test_attempt_question_returns_message_on_wrong_answer(): void
    {
        $question = Question::factory()->create();
        $question->answers()->create(['text' => 'correctanswer']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptQuestion', $question->id), ['attempt' => 'wronganswer']);

        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('incorrect_answers', ['answer' => 'wronganswer']);
    }

    
    public function test_attempt_question_does_not_duplicate_correct_answer_record(): void
    {
        $question = Question::factory()->create();
        $question->answers()->create(['text' => 'correctanswer']);
        $question->completedBy()->attach($this->team->id);

        $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptQuestion', $question->id), ['attempt' => 'correctanswer']);

        $this->assertCount(1, $question->completedBy()->where('team_id', $this->team->id)->get());
    }

    // -------------------------------------------------------------------------
    // attemptMinigame
    // -------------------------------------------------------------------------

    
    public function test_attempt_minigame_validates_attempt_field(): void
    {
        $quest = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptMinigame', $quest->id), []);

        $response->assertStatus(422);
    }

    
    public function test_attempt_minigame_returns_correct_true_for_ascending_order(): void
    {
        $quest  = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);
        $image1 = MinigameImage::factory()->create(['quest_id' => $quest->id, 'year' => 2000]);
        $image2 = MinigameImage::factory()->create(['quest_id' => $quest->id, 'year' => 2010]);
        $correct = $image1->id.','.$image2->id;

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptMinigame', $quest->id), ['attempt' => $correct]);

        $response->assertJson(['correct' => true]);
    }

    
    public function test_attempt_minigame_returns_correct_true_for_descending_order(): void
    {
        $quest  = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);
        $image1 = MinigameImage::factory()->create(['quest_id' => $quest->id, 'year' => 2000]);
        $image2 = MinigameImage::factory()->create(['quest_id' => $quest->id, 'year' => 2010]);
        $correct = $image2->id.','.$image1->id;

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptMinigame', $quest->id), ['attempt' => $correct]);

        $response->assertJson(['correct' => true]);
    }

    
    public function test_attempt_minigame_returns_empty_response_for_wrong_order(): void
    {
        $quest  = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);
        MinigameImage::factory()->create(['quest_id' => $quest->id, 'year' => 2000]);
        MinigameImage::factory()->create(['quest_id' => $quest->id, 'year' => 2010]);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptMinigame', $quest->id), ['attempt' => '999,998']);

        $response->assertExactJson([]);
    }

    // -------------------------------------------------------------------------
    // dna
    // -------------------------------------------------------------------------

    
    public function test_dna_returns_200_and_correct_view(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.dna'));

        $response->assertStatus(200);
        $response->assertViewIs('ui.dna');
        $response->assertViewHasAll(['team', 'sequences']);
    }

    
    public function test_dna_marks_sequence_as_collected_when_team_has_found_it(): void
    {
        $dna = GhostDna::factory()->create(['pair' => 1]);
        $this->team->foundDna()->attach($dna->id);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->get(route('ui.dna'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // attemptDna
    // -------------------------------------------------------------------------

    
    public function test_attempt_dna_validates_attempt_field(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptDna'), []);

        $response->assertStatus(422);
    }

    
    public function test_attempt_dna_returns_correct_true_on_valid_sequence(): void
    {
        $sibling = GhostDna::factory()->create(['pair' => 1, 'sequence' => 'SIBLING']);
        $dna     = GhostDna::factory()->create(['pair' => 1, 'sequence' => 'TESTSEQ']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptDna'), ['attempt' => 'TESTSEQ']);

        $response->assertJsonStructure(['correct', 'dnaId', 'topOrBottom']);
        $response->assertJson(['correct' => true]);
    }

    
    public function test_attempt_dna_returns_message_when_already_collected(): void
    {
        $dna = GhostDna::factory()->create(['pair' => 1, 'sequence' => 'TESTSEQ']);
        $this->team->foundDna()->attach($dna->id);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptDna'), ['attempt' => 'TESTSEQ']);

        $response->assertJsonStructure(['message']);
    }

    
    public function test_attempt_dna_returns_empty_response_for_unknown_sequence(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.attemptDna'), ['attempt' => 'NOTFOUND']);

        $response->assertExactJson([]);
    }

    // -------------------------------------------------------------------------
    // setEvidence
    // -------------------------------------------------------------------------

    
    public function test_set_evidence_validates_evidence_field(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.setEvidence'), []);

        $response->assertStatus(422);
    }

    
    public function test_set_evidence_associates_evidence_with_team(): void
    {
        $evidence = Evidence::factory()->create();

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.setEvidence'), ['evidence' => $evidence->id]);

        $response->assertExactJson([]);
        $this->assertDatabaseHas('teams', [
            'id'          => $this->team->id,
            'evidence_id' => $evidence->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // questStatus
    // -------------------------------------------------------------------------

    
    public function test_quest_status_returns_incomplete_for_question_quest_with_no_answers(): void
    {
        $quest = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'question']);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->getJson(route('ui.questStatus', $quest->id));

        $response->assertJson(['status' => 'incomplete']);
    }

    
    public function test_quest_status_returns_complete_for_question_quest_with_enough_answers(): void
    {
        $quest     = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'question']);
        $questions = Question::factory()->count(3)->create(['quest_id' => $quest->id]);
        foreach ($questions as $q) {
            $q->completedBy()->attach($this->team->id);
        }

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->getJson(route('ui.questStatus', $quest->id));

        $response->assertJson(['status' => 'complete']);
    }

    
    public function test_quest_status_returns_complete_for_minigame_quest_already_completed(): void
    {
        $quest = Quest::factory()->create(['game_id' => $this->game->id, 'type' => 'minigame']);
        $this->team->completedQuests()->attach($quest->id);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->getJson(route('ui.questStatus', $quest->id));

        $response->assertJson(['status' => 'complete']);
    }

    
    public function test_quest_status_returns_404_for_invalid_id(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->getJson(route('ui.questStatus', 9999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // alert
    // -------------------------------------------------------------------------

    
    public function test_alert_returns_empty_when_no_alerts(): void
    {
        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->getJson(route('ui.alert'));

        $response->assertExactJson([]);
    }

    
    public function test_alert_returns_html_for_unread_alert(): void
    {
        $alert = Alert::factory()->create(['game_id' => $this->game->id]);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->getJson(route('ui.alert'));

        $response->assertJsonStructure(['html']);
    }

    
    public function test_alert_skips_already_seen_alerts(): void
    {
        $alert = Alert::factory()->create(['game_id' => $this->game->id]);

        $response = $this->actingAs($this->player, 'player')
            ->withSession(array_merge($this->sessionData(), ['seen' => [$alert->id]]))
            ->getJson(route('ui.alert'));

        $response->assertExactJson([]);
    }

    // -------------------------------------------------------------------------
    // seen
    // -------------------------------------------------------------------------

    
    public function test_seen_adds_alert_id_to_session_and_returns_success(): void
    {
        $alert = Alert::factory()->create(['game_id' => $this->game->id]);

        $response = $this->actingAs($this->player, 'player')
            ->withSession($this->sessionData())
            ->postJson(route('ui.alert.seen', $alert->id));

        $response->assertStatus(200);
        $response->assertJson(['success']);
    }
}

