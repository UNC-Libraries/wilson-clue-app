<?php

namespace Tests\Unit;

use App\Game;
use App\Player;
use App\Team;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Attributes / Casts
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $player = new Player();

        $this->assertEquals([
            'first_name',
            'last_name',
            'onyen',
            'pid',
            'email',
            'academic_group_code',
            'class_code',
            'password',
            'manual',
            'student',
            'checked_in',
        ], $player->getFillable());
    }

    public function test_it_appends_expected_derived_fields_in_array_output(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'jane',
            'last_name' => 'doe',
            'class_code' => 'UGRD',
            'academic_group_code' => 'CAS',
        ]);

        $data = $player->toArray();

        $this->assertArrayHasKey('full_name', $data);
        $this->assertArrayHasKey('class', $data);
        $this->assertArrayHasKey('academic_group', $data);
        $this->assertEquals('Jane Doe', $data['full_name']);
        $this->assertEquals('Undergraduate', $data['class']);
        $this->assertEquals('College of Arts and Sciences', $data['academic_group']);
    }

    public function test_it_sets_default_class_and_academic_group_codes(): void
    {
        $player = new Player();

        $this->assertEquals('NONS', $player->getAttributes()['class_code']);
        $this->assertEquals('NONS', $player->getAttributes()['academic_group_code']);
    }

    public function test_it_casts_student_and_manual_to_booleans(): void
    {
        $player = Player::factory()->create([
            'student' => 1,
            'manual' => 0,
        ]);

        $this->assertIsBool($player->student);
        $this->assertIsBool($player->manual);
        $this->assertTrue($player->student);
        $this->assertFalse($player->manual);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_a_teams_belongs_to_many_relationship(): void
    {
        $player = new Player();

        $this->assertInstanceOf(BelongsToMany::class, $player->teams());
        $this->assertInstanceOf(Team::class, $player->teams()->getRelated());
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function checked_in_scope_returns_only_checked_in_players(): void
    {
        $checkedIn = Player::factory()->create(['checked_in' => true]);
        Player::factory()->create(['checked_in' => false]);

        $results = Player::query()->checkedIn()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($checkedIn->id, $results->first()->id);
    }

    public function of_game_scope_returns_only_players_on_teams_for_the_given_game(): void
    {
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();

        $teamA = Team::factory()->create(['game_id' => $gameA->id]);
        $teamB = Team::factory()->create(['game_id' => $gameB->id]);

        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $teamA->players()->attach($playerA->id);
        $teamB->players()->attach($playerB->id);

        $results = Player::query()->ofGame($gameA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($playerA->id, $results->first()->id);
    }

    // -------------------------------------------------------------------------
    // Accessors / Mutators
    // -------------------------------------------------------------------------

    public function test_it_formats_name_accessors_and_full_name(): void
    {
        $player = new Player();
        $player->first_name = 'jANE';
        $player->last_name = 'dOE';

        $this->assertEquals('Jane', $player->first_name);
        $this->assertEquals('Doe', $player->last_name);
        $this->assertEquals('Jane Doe', $player->full_name);
    }

    public function test_it_maps_class_and_academic_group_codes_to_labels(): void
    {
        $player = new Player();
        $player->class_code = 'MED';
        $player->academic_group_code = 'SOM';

        $this->assertEquals('Medical', $player->class);
        $this->assertEquals('School of Medicine', $player->academic_group);
    }

    public function test_it_maps_empty_class_and_academic_group_codes_to_not_found(): void
    {
        $player = new Player();
        $player->class_code = null;
        $player->academic_group_code = null;

        $this->assertEquals('Not Found', $player->class);
        $this->assertEquals('Not Found', $player->academic_group);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function get_warnings_returns_onyen_not_found_when_onyen_is_invalid(): void
    {
        /** @var Game $game */
        $game = Game::factory()->create(['students_only' => false]);

        $player = $this->getMockBuilder(Player::class)
            ->onlyMethods(['validOnyen'])
            ->getMock();

        $player->onyen = 'badonyen';
        $player->checked_in = false;
        $player->student = true;
        $player->setRelation('teams', collect());
        $player->method('validOnyen')->willReturn(false);

        $warnings = $player->getWarnings($game);

        $this->assertContains('enlist.add_player.onyen_not_found', $warnings);
    }

    public function get_warnings_returns_previous_when_player_is_already_checked_in(): void
    {
        /** @var Game $game */
        $game = Game::factory()->create(['students_only' => false]);

        $player = $this->getMockBuilder(Player::class)
            ->onlyMethods(['validOnyen'])
            ->getMock();

        $player->onyen = 'goodonyen';
        $player->checked_in = true;
        $player->student = true;
        $player->setRelation('teams', collect());
        $player->method('validOnyen')->willReturn(true);

        $warnings = $player->getWarnings($game);

        $this->assertContains('enlist.add_player.previous', $warnings);
    }

    public function get_warnings_returns_current_when_player_is_already_registered_for_game(): void
    {
        /** @var Game $game */
        $game = Game::factory()->create(['students_only' => false]);
        $team = Team::factory()->create(['game_id' => $game->id]);

        $player = $this->getMockBuilder(Player::class)
            ->onlyMethods(['validOnyen'])
            ->getMock();

        $player->onyen = 'goodonyen';
        $player->checked_in = false;
        $player->student = true;
        $player->setRelation('teams', collect([$team]));
        $player->method('validOnyen')->willReturn(true);

        $warnings = $player->getWarnings($game);

        $this->assertContains('enlist.add_player.current', $warnings);
    }

    public function get_warnings_returns_not_student_for_students_only_game_when_player_is_not_student(): void
    {
        /** @var Game $game */
        $game = Game::factory()->create(['students_only' => true]);

        $player = $this->getMockBuilder(Player::class)
            ->onlyMethods(['validOnyen'])
            ->getMock();

        $player->onyen = 'goodonyen';
        $player->checked_in = false;
        $player->student = false;
        $player->setRelation('teams', collect());
        $player->method('validOnyen')->willReturn(true);

        $warnings = $player->getWarnings($game);

        $this->assertContains('enlist.add_player.not_student', $warnings);
    }

    public function get_warnings_returns_empty_array_when_no_warning_conditions_are_met(): void
    {
        /** @var Game $game */
        $game = Game::factory()->create(['students_only' => false]);

        $player = $this->getMockBuilder(Player::class)
            ->onlyMethods(['validOnyen'])
            ->getMock();

        $player->onyen = 'goodonyen';
        $player->checked_in = false;
        $player->student = true;
        $player->setRelation('teams', collect());
        $player->method('validOnyen')->willReturn(true);

        $warnings = $player->getWarnings($game);

        $this->assertSame([], $warnings);
    }

    public function get_warnings_handles_null_game_parameter(): void
    {
        $player = $this->getMockBuilder(Player::class)
            ->onlyMethods(['validOnyen'])
            ->getMock();

        $player->onyen = 'goodonyen';
        $player->checked_in = false;
        $player->student = false;
        $player->setRelation('teams', collect());
        $player->method('validOnyen')->willReturn(true);

        $warnings = $player->getWarnings(null);

        $this->assertSame([], $warnings);
    }

    // -------------------------------------------------------------------------
    // Mutators
    // -------------------------------------------------------------------------

    public function test_set_first_name_lowercases_and_encodes_value(): void
    {
        $player = new Player();
        $player->first_name = 'JOHN';

        $this->assertEquals('john', $player->getAttributes()['first_name']);
    }

    public function test_set_last_name_lowercases_and_encodes_value(): void
    {
        $player = new Player();
        $player->last_name = 'DOE';

        $this->assertEquals('doe', $player->getAttributes()['last_name']);
    }

    public function test_set_first_name_handles_unicode_characters(): void
    {
        $player = new Player();
        $player->first_name = 'José';

        $this->assertStringContainsString('josé', strtolower($player->getAttributes()['first_name']));
    }

    public function test_set_class_code_converts_null_to_empty_string(): void
    {
        $player = new Player();
        $player->class_code = null;

        $this->assertEquals('', $player->getAttributes()['class_code']);
    }

    public function test_set_class_code_preserves_valid_value(): void
    {
        $player = new Player();
        $player->class_code = 'UGRD';

        $this->assertEquals('UGRD', $player->getAttributes()['class_code']);
    }

    public function test_set_academic_group_code_converts_null_to_empty_string(): void
    {
        $player = new Player();
        $player->academic_group_code = null;

        $this->assertEquals('', $player->getAttributes()['academic_group_code']);
    }

    public function test_set_academic_group_code_preserves_valid_value(): void
    {
        $player = new Player();
        $player->academic_group_code = 'CAS';

        $this->assertEquals('CAS', $player->getAttributes()['academic_group_code']);
    }

    // -------------------------------------------------------------------------
    // LDAP Methods
    // -------------------------------------------------------------------------

    public function test_get_ldap_domain_column_returns_onyen(): void
    {
        $player = new Player();

        $this->assertEquals('onyen', $player->getLdapDomainColumn());
    }

    public function test_get_ldap_guid_column_returns_objectguid(): void
    {
        $player = new Player();

        $this->assertEquals('objectguid', $player->getLdapGuidColumn());
    }

    // -------------------------------------------------------------------------
    // All Class Options
    // -------------------------------------------------------------------------

    public function test_class_accessor_maps_all_valid_codes(): void
    {
        $expectations = [
            'UGRD' => 'Undergraduate',
            'GRAD' => 'Graduate',
            'MED' => 'Medical',
            'DENT' => 'Dental',
            'LAW' => 'Law',
            'PHCY' => 'Pharmacy',
            'NONS' => 'Non Student',
            '' => 'Not Found',
        ];

        foreach ($expectations as $code => $label) {
            $player = new Player();
            $player->class_code = $code;
            $this->assertEquals($label, $player->class, "Failed for class code: {$code}");
        }
    }

    // -------------------------------------------------------------------------
    // All Academic Group Options
    // -------------------------------------------------------------------------

    public function test_academic_group_accessor_maps_all_valid_codes(): void
    {
        $expectations = [
            'LAW' => 'School of Law',
            'GRAD' => 'Graduate School',
            'SPH' => 'School of Public Health',
            'SSW' => 'School of Social Work',
            'SOP' => 'School of Pharmacy',
            'SOM' => 'School of Medicine',
            'CAS' => 'College of Arts and Sciences',
            'SOE' => 'School of Education',
            'KFBS' => 'Kenan-Flagler Business School',
            'SON' => 'School of Nursing',
            'SILS' => 'School of Information and Library Science',
            'SOJ' => 'School of Journalism',
            'SOG' => 'School of Government',
            'SOD' => 'School of Dentistry',
            'OUR' => 'Office of the University Registrar',
            'NONS' => 'Non Student',
            '' => 'Not Found',
        ];

        foreach ($expectations as $code => $label) {
            $player = new Player();
            $player->academic_group_code = $code;
            $this->assertEquals($label, $player->academic_group, "Failed for academic group code: {$code}");
        }
    }

    // -------------------------------------------------------------------------
    // Edge Cases
    // -------------------------------------------------------------------------

    public function test_first_name_accessor_handles_empty_string(): void
    {
        $player = new Player();
        $player->setRawAttributes(['first_name' => '']);

        $this->assertEquals('', $player->first_name);
    }

    public function test_last_name_accessor_handles_empty_string(): void
    {
        $player = new Player();
        $player->setRawAttributes(['last_name' => '']);

        $this->assertEquals('', $player->last_name);
    }

    public function test_full_name_handles_empty_names(): void
    {
        $player = new Player();
        $player->setRawAttributes(['first_name' => '', 'last_name' => '']);

        $this->assertEquals(' ', $player->full_name);
    }

    public function test_player_can_be_created_with_factory(): void
    {
        $player = Player::factory()->create([
            'first_name' => 'test',
            'last_name' => 'user',
            'onyen' => 'testuser',
        ]);

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'onyen' => 'testuser',
        ]);
    }

    public function test_player_can_be_attached_to_multiple_teams(): void
    {
        $player = Player::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $player->teams()->attach([$team1->id, $team2->id]);

        $this->assertCount(2, $player->fresh()->teams);
        $this->assertTrue($player->teams->pluck('id')->contains($team1->id));
        $this->assertTrue($player->teams->pluck('id')->contains($team2->id));
    }

    public function test_player_can_be_detached_from_team(): void
    {
        $player = Player::factory()->create();
        $team = Team::factory()->create();

        $player->teams()->attach($team->id);
        $this->assertCount(1, $player->fresh()->teams);

        $player->teams()->detach($team->id);
        $this->assertCount(0, $player->fresh()->teams);
    }

    public function test_checked_in_defaults_to_false_when_not_set(): void
    {
        $player = Player::factory()->create(['checked_in' => null]);

        $this->assertFalse($player->checked_in);
    }

    public function test_manual_defaults_to_false_when_not_set(): void
    {
        $player = Player::factory()->create(['manual' => null]);

        $this->assertFalse($player->manual);
    }

    public function test_student_defaults_to_false_when_not_set(): void
    {
        $player = Player::factory()->create(['student' => null]);

        $this->assertFalse($player->student);
    }

    public function test_password_is_fillable(): void
    {
        $player = new Player(['password' => 'testpassword']);

        $this->assertEquals('testpassword', $player->password);
    }

    public function test_pid_is_fillable(): void
    {
        $player = new Player(['pid' => '123456789']);

        $this->assertEquals('123456789', $player->pid);
    }

    public function test_email_is_fillable(): void
    {
        $player = new Player(['email' => 'test@example.com']);

        $this->assertEquals('test@example.com', $player->email);
    }
}


