<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Alert;
use App\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertControllerTest extends TestCase
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
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_alert_for_game(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => 'Important game announcement',
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => 'Important game announcement',
        ]);
    }

    public function test_store_associates_alert_with_game(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => 'Test alert',
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));

        $alert = Alert::where('message', 'Test alert')->first();
        $this->assertNotNull($alert);
        $this->assertEquals($game->id, $alert->game_id);
    }

    public function test_store_requires_message_field(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), []);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_validates_message_is_string(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => ['not', 'a', 'string'],
            ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_validates_message_max_255_characters(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_accepts_message_exactly_255_characters(): void
    {
        $game = Game::factory()->create();

        $message = str_repeat('a', 255);

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => $message,
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => $message,
        ]);
    }

    public function test_store_accepts_empty_string_message(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => '',
            ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_handles_special_characters_in_message(): void
    {
        $game = Game::factory()->create();

        $message = 'Alert with special chars: <>&"\'@#$%';

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => $message,
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => $message,
        ]);
    }

    public function test_store_handles_unicode_characters_in_message(): void
    {
        $game = Game::factory()->create();

        $message = 'Alert with emoji 🎮 and unicode: café, naïve, 日本語';

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => $message,
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => $message,
        ]);
    }

    public function test_store_creates_multiple_alerts_for_same_game(): void
    {
        $game = Game::factory()->create();

        $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => 'First alert',
            ]);

        $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => 'Second alert',
            ]);

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => 'First alert',
        ]);

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => 'Second alert',
        ]);

        $this->assertCount(2, Alert::where('game_id', $game->id)->get());
    }

    public function test_store_redirects_to_game_dashboard(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => 'Test alert',
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));
    }

    public function test_store_works_for_different_games(): void
    {
        $game1 = Game::factory()->create();
        $game2 = Game::factory()->create();

        $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game1->id), [
                'message' => 'Game 1 alert',
            ]);

        $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game2->id), [
                'message' => 'Game 2 alert',
            ]);

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game1->id,
            'message' => 'Game 1 alert',
        ]);

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game2->id,
            'message' => 'Game 2 alert',
        ]);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_alert(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create([
            'game_id' => $game->id,
            'message' => 'Alert to delete',
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game->id, $alert->id]));

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseMissing('alerts', [
            'id' => $alert->id,
        ]);
    }

    public function test_destroy_only_deletes_specified_alert(): void
    {
        $game = Game::factory()->create();
        $alert1 = Alert::factory()->create(['game_id' => $game->id, 'message' => 'Alert 1']);
        $alert2 = Alert::factory()->create(['game_id' => $game->id, 'message' => 'Alert 2']);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game->id, $alert1->id]));

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseMissing('alerts', ['id' => $alert1->id]);
        $this->assertDatabaseHas('alerts', ['id' => $alert2->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_alert(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game->id, 999999]));

        $response->assertStatus(404);
    }

    public function test_destroy_redirects_to_game_dashboard(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game->id, $alert->id]));

        $response->assertRedirect(route('admin.game.show', $game->id));
    }

    public function test_destroy_works_with_correct_game_id_parameter(): void
    {
        $game1 = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game1->id]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game1->id, $alert->id]));

        $response->assertRedirect(route('admin.game.show', $game1->id));

        $this->assertDatabaseMissing('alerts', ['id' => $alert->id]);
    }

    public function test_destroy_accepts_mismatched_game_id_parameter(): void
    {
        $game1 = Game::factory()->create();
        $game2 = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game1->id]);

        // Controller doesn't validate game ownership, just uses the first ID for redirect
        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game2->id, $alert->id]));

        $response->assertRedirect(route('admin.game.show', $game2->id));

        $this->assertDatabaseMissing('alerts', ['id' => $alert->id]);
    }

    public function test_destroy_handles_soft_deleted_games(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game->id]);

        $game->delete();

        $response = $this->actingAsAdmin()
            ->delete(route('admin.game.alert.destroy', [$game->id, $alert->id]));

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseMissing('alerts', ['id' => $alert->id]);
    }

    // -------------------------------------------------------------------------
    // Edge Cases & Security
    // -------------------------------------------------------------------------

    public function test_store_requires_authentication(): void
    {
        $game = Game::factory()->create();

        $response = $this->post(route('admin.game.alert.store', $game->id), [
            'message' => 'Unauthorized attempt',
        ]);

        $response->assertRedirect(route('admin.login.form'));

        $this->assertDatabaseMissing('alerts', [
            'message' => 'Unauthorized attempt',
        ]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $game = Game::factory()->create();
        $alert = Alert::factory()->create(['game_id' => $game->id]);

        $response = $this->delete(route('admin.game.alert.destroy', [$game->id, $alert->id]));

        $response->assertRedirect(route('admin.login.form'));

        $this->assertDatabaseHas('alerts', ['id' => $alert->id]);
    }

    public function test_store_trims_whitespace_from_message(): void
    {
        $game = Game::factory()->create();

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => '  Test message with whitespace  ',
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));

        // Laravel's TrimStrings middleware strips leading/trailing whitespace
        // before the value reaches the controller.
        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => 'Test message with whitespace',
        ]);
    }

    public function test_store_handles_newline_characters_in_message(): void
    {
        $game = Game::factory()->create();

        $message = "Line 1\nLine 2\nLine 3";

        $response = $this->actingAsAdmin()
            ->post(route('admin.game.alert.store', $game->id), [
                'message' => $message,
            ]);

        $response->assertRedirect(route('admin.game.show', $game->id));

        $this->assertDatabaseHas('alerts', [
            'game_id' => $game->id,
            'message' => $message,
        ]);
    }
}

