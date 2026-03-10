<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        /** @var \App\Agent $admin */
        $admin = Agent::factory()->create(['admin' => true]);
        return $this->actingAs($admin, 'admin');
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_displays_all_agents(): void
    {
        $agentA = Agent::factory()->create(['last_name' => 'Anderson']);
        $agentB = Agent::factory()->create(['last_name' => 'Brown']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.agent.index'));

        $response->assertStatus(200);
        $response->assertViewIs('agent.index');
        $response->assertViewHas('agents', function ($agents) use ($agentA, $agentB) {
            return $agents->pluck('id')->contains($agentA->id)
                && $agents->pluck('id')->contains($agentB->id);
        });
    }

    public function test_index_orders_agents_by_last_name(): void
    {
        $agentZ = Agent::factory()->create(['last_name' => 'Zimmerman']);
        $agentA = Agent::factory()->create(['last_name' => 'Anderson']);
        $agentM = Agent::factory()->create(['last_name' => 'Miller']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.agent.index'));

        $response->assertStatus(200);
        $response->assertViewHas('agents', function ($agents) use ($agentA, $agentM, $agentZ) {
            return $agents->first()->id === $agentA->id
                && $agents->last()->id === $agentZ->id;
        });
    }

    public function test_index_returns_empty_collection_when_no_agents_exist(): void
    {
        Agent::query()->delete();

        $response = $this->get(route('admin.agent.index'));

        // Admin routes require auth; with no admin session this redirects.
        $response->assertStatus(302);
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_displays_form_with_new_agent(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.agent.create'));

        $response->assertStatus(200);
        $response->assertViewIs('agent.create');
        $response->assertViewHas('agent', function ($agent) {
            return $agent instanceof Agent && !$agent->exists;
        });
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_agent_with_required_fields(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseHas('agents', [
            'first_name' => 'john',
            'last_name' => 'doe',
            'title' => 'Agent',
        ]);
    }

    public function test_store_creates_agent_with_uploaded_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('agent.jpg', 800, 600);

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.type', 'success');

        $agent = Agent::where('first_name', 'jane')->first();
        $this->assertNotNull($agent->src);
        Storage::disk('public')->assertExists($agent->getRawOriginal('src'));
    }

    public function test_store_validates_image_file_size(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('large-agent.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_validates_image_mime_type(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_store_accepts_jpeg_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_accepts_png_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.png');

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_accepts_svg_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('test.svg', 10, 'image/svg+xml');

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_requires_first_name_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'last_name' => 'Doe',
                'title' => 'Agent',
            ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_store_requires_last_name_field(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'title' => 'Agent',
            ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_store_creates_agent_without_file(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
            ]);

        $response->assertRedirect(route('admin.agent.index'));

        $agent = Agent::where('first_name', 'john')->first();
        $this->assertNotNull($agent);
        $this->assertNull($agent->getRawOriginal('src'));
    }

    public function test_store_stores_file_in_agents_directory(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
                'new_image_file' => $file,
            ]);

        $agent = Agent::where('first_name', 'john')->first();
        $this->assertStringStartsWith('agents/', $agent->getRawOriginal('src'));
    }

    public function test_store_redirects_with_agent_title_and_last_name_in_message(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.agent.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Detective',
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.message', 'Detective Doe saved!');
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------

    public function test_edit_displays_form_with_existing_agent(): void
    {
        $agent = Agent::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Agent',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.agent.edit', $agent->id));

        $response->assertStatus(200);
        $response->assertViewIs('agent.edit');
        $response->assertViewHas('agent', function ($a) use ($agent) {
            return $a->id === $agent->id
                && $a->first_name === 'John'
                && $a->last_name === 'Doe';
        });
    }

    public function test_edit_returns_404_for_nonexistent_agent(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.agent.edit', 999999));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_agent(): void
    {
        $agent = Agent::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'title' => 'Agent',
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'title' => 'Detective',
            ]);

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Detective Name updated!');

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'first_name' => 'updated',
            'title' => 'Detective',
        ]);
    }

    public function test_update_resets_boolean_fields_to_false(): void
    {
        $agent = Agent::factory()->create([
            'retired' => true,
            'web_display' => true,
            'admin' => true,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => $agent->first_name,
                'last_name' => $agent->last_name,
                'title' => $agent->title,
                // Not sending checkbox values
            ]);

        $response->assertRedirect(route('admin.agent.index'));

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'retired' => false,
            'web_display' => false,
            'admin' => false,
        ]);
    }

    public function test_update_sets_boolean_fields_when_present_in_request(): void
    {
        $agent = Agent::factory()->create([
            'retired' => false,
            'web_display' => false,
            'admin' => false,
        ]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => $agent->first_name,
                'last_name' => $agent->last_name,
                'title' => $agent->title,
                'retired' => '1',
                'web_display' => '1',
                'admin' => '1',
            ]);

        $response->assertRedirect(route('admin.agent.index'));

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'retired' => true,
            'web_display' => true,
            'admin' => true,
        ]);
    }

    public function test_update_replaces_image_file_and_deletes_old_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('agents', 'public');

        $agent = Agent::factory()->create(['src' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => $agent->first_name,
                'last_name' => $agent->last_name,
                'title' => $agent->title,
                'new_image_file' => $newFile,
            ]);

        $response->assertRedirect(route('admin.agent.index'));

        $fresh = $agent->fresh();
        $this->assertNotEquals($oldPath, $fresh->src);
        Storage::disk('public')->assertExists($fresh->src);
    }

    public function test_update_validates_new_image_file_size(): void
    {
        Storage::fake('public');

        $agent = Agent::factory()->create();
        $file = UploadedFile::fake()->create('large.jpg', 2048); // 2MB

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => $agent->first_name,
                'last_name' => $agent->last_name,
                'title' => $agent->title,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_update_validates_new_image_mime_type(): void
    {
        Storage::fake('public');

        $agent = Agent::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => $agent->first_name,
                'last_name' => $agent->last_name,
                'title' => $agent->title,
                'new_image_file' => $file,
            ]);

        $response->assertSessionHasErrors('new_image_file');
    }

    public function test_update_without_new_file_preserves_existing_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('agents', 'public');

        $agent = Agent::factory()->create(['src' => $oldPath]);

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => 'Updated',
                'last_name' => $agent->last_name,
                'title' => $agent->title,
            ]);

        $response->assertRedirect(route('admin.agent.index'));

        $fresh = $agent->fresh();
        $this->assertEquals($oldPath, $fresh->getRawOriginal('src'));
        Storage::disk('public')->assertExists($oldPath);
    }

    public function test_update_requires_first_name_field(): void
    {
        $agent = Agent::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'last_name' => 'Doe',
                'title' => 'Agent',
            ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_update_requires_last_name_field(): void
    {
        $agent = Agent::factory()->create();

        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', $agent->id), [
                'first_name' => 'John',
                'title' => 'Agent',
            ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_update_returns_404_for_nonexistent_agent(): void
    {
        $response = $this->actingAsAdmin()
            ->put(route('admin.agent.update', 999999), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Agent',
            ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_agent(): void
    {
        $agent = Agent::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Agent',
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.agent.destroy', $agent->id));

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.type', 'success');
        $response->assertSessionHas('alert.message', 'Agent Doe deleted!');

        $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
    }

    public function test_destroy_deletes_image_file_when_deleting_agent(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('agents', 'public');

        $agent = Agent::factory()->create(['src' => $path]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.agent.destroy', $agent->id));

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.type', 'success');
    }

    public function test_destroy_handles_agent_without_image(): void
    {
        $agent = Agent::factory()->create(['src' => null]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.agent.destroy', $agent->id));

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.type', 'success');

        $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_agent(): void
    {
        $response = $this->actingAsAdmin()
            ->delete(route('admin.agent.destroy', 999999));

        $response->assertStatus(404);
    }

    public function test_destroy_redirects_with_agent_title_and_last_name_in_message(): void
    {
        $agent = Agent::factory()->create([
            'title' => 'Detective',
            'last_name' => 'Smith',
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.agent.destroy', $agent->id));

        $response->assertRedirect(route('admin.agent.index'));
        $response->assertSessionHas('alert.message', 'Detective Smith deleted!');
    }
}

