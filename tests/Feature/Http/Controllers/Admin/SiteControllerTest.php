<?php

namespace Tests\Feature\Http\Controllers\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        /** @var \App\Agent $admin */
        $admin = \App\Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // updateHomePageAlert
    // -------------------------------------------------------------------------

    public function test_update_home_page_alert_creates_homepage_record_when_it_does_not_exist(): void
    {
        $this->assertDatabaseMissing('globals', ['key' => 'homepage']);

        DB::table('globals')->insert(['key' => 'homepage', 'message' => 'Original message']);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => 'New alert message',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'New alert message',
        ]);
    }

    public function test_update_home_page_alert_updates_existing_homepage_record(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original alert message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => 'Updated alert message',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'Updated alert message',
        ]);

        $this->assertDatabaseMissing('globals', [
            'key' => 'homepage',
            'message' => 'Original alert message',
        ]);
    }

    public function test_update_home_page_alert_handles_empty_string(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Some message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => '',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => '',
        ]);
    }

    public function test_update_home_page_alert_handles_null_input(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Some message',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => null,
            ]);

        $response->assertRedirect();

        $record = DB::table('globals')->where('key', 'homepage')->first();
        $this->assertEmpty($record->message);
    }

    public function test_update_home_page_alert_handles_html_content(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $htmlContent = '<strong>Important!</strong> Game starts at <em>6:30 PM</em>';

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => $htmlContent,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => $htmlContent,
        ]);
    }

    public function test_update_home_page_alert_handles_long_text(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Short',
        ]);

        $longText = str_repeat('This is a very long alert message. ', 100);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => $longText,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => $longText,
        ]);
    }

    public function test_update_home_page_alert_handles_special_characters(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $specialChars = 'Alert with special chars: @#$%^&*()_+-=[]{}|;:\'",.<>?/\\`~';

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => $specialChars,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => $specialChars,
        ]);
    }

    public function test_update_home_page_alert_handles_unicode_characters(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $unicodeText = 'Alert with emoji 🎮🔍 and unicode: café, naïve, 日本語';

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => $unicodeText,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => $unicodeText,
        ]);
    }

    public function test_update_home_page_alert_does_not_affect_other_global_keys(): void
    {
        DB::table('globals')->insert([
            ['key' => 'homepage', 'message' => 'Original homepage'],
            ['key' => 'special_notice', 'message' => 'Special notice text'],
            ['key' => 'registration_closed', 'message' => 'Registration closed text'],
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => 'New homepage message',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'New homepage message',
        ]);

        $this->assertDatabaseHas('globals', [
            'key' => 'special_notice',
            'message' => 'Special notice text',
        ]);

        $this->assertDatabaseHas('globals', [
            'key' => 'registration_closed',
            'message' => 'Registration closed text',
        ]);
    }

    public function test_update_home_page_alert_redirects_back_to_previous_page(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $response = $this->actingAsAdmin()
            ->from(route('admin.siteMessages'))
            ->post(route('admin.siteMessages.updateHomePageAlert'), [
                'homepage-alert' => 'Updated message',
            ]);

        $response->assertRedirect(route('admin.siteMessages'));
    }

    public function test_update_home_page_alert_requires_authentication(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $response = $this->post(route('admin.siteMessages.updateHomePageAlert'), [
            'homepage-alert' => 'Unauthorized attempt',
        ]);

        $response->assertRedirect(route('admin.login.form'));

        $this->assertDatabaseHas('globals', [
            'key' => 'homepage',
            'message' => 'Original',
        ]);
    }

    public function test_update_home_page_alert_handles_missing_input_key(): void
    {
        DB::table('globals')->insert([
            'key' => 'homepage',
            'message' => 'Original',
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.siteMessages.updateHomePageAlert'), []);

        $response->assertRedirect();

        $record = DB::table('globals')->where('key', 'homepage')->first();
        $this->assertEmpty($record->message);
    }
}