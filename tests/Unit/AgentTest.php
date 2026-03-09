<?php

namespace Tests\Unit;

use App\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AgentTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    
    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $agent = new Agent();

        $this->assertEquals([
            'onyen',
            'first_name',
            'last_name',
            'job_title',
            'title',
            'location',
            'retired',
            'bio',
            'web_display',
            'admin',
            'src',
        ], $agent->getFillable());
    }

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    
    public function test_it_casts_retired_to_boolean(): void
    {
        $agent = factory(Agent::class)->create(['retired' => 1]);

        $this->assertIsBool($agent->retired);
        $this->assertTrue($agent->retired);
    }

    
    public function test_it_casts_web_display_to_boolean(): void
    {
        $agent = factory(Agent::class)->create(['web_display' => 1]);

        $this->assertIsBool($agent->web_display);
        $this->assertTrue($agent->web_display);
    }

    
    public function test_it_casts_admin_to_boolean(): void
    {
        $agent = factory(Agent::class)->create(['admin' => 1]);

        $this->assertIsBool($agent->admin);
        $this->assertTrue($agent->admin);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    
    public function test_it_returns_ucfirst_first_name(): void
    {
        $agent = factory(Agent::class)->make(['first_name' => 'john']);

        $this->assertEquals('John', $agent->first_name);
    }

    
    public function test_it_returns_ucfirst_last_name(): void
    {
        $agent = factory(Agent::class)->make(['last_name' => 'doe']);

        $this->assertEquals('Doe', $agent->last_name);
    }

    
    public function test_it_returns_full_name_as_concatenation_of_first_and_last(): void
    {
        $agent = factory(Agent::class)->make([
            'first_name' => 'john',
            'last_name'  => 'doe',
        ]);

        $this->assertEquals('John Doe', $agent->full_name);
    }

    
    public function test_it_prepends_public_uploads_path_to_src(): void
    {
        $agent = factory(Agent::class)->make(['src' => 'agents/photo.jpg']);

        $expected = env('PUBLIC_UPLOADS_PATH').'/agents/photo.jpg';

        $this->assertEquals($expected, $agent->src);
    }

    // -------------------------------------------------------------------------
    // Mutators
    // -------------------------------------------------------------------------

    
    public function test_it_stores_first_name_as_lowercase(): void
    {
        $agent = new Agent();
        $agent->first_name = 'JOHN';

        $this->assertEquals('john', $agent->getAttributes()['first_name']);
    }

    
    public function test_it_stores_last_name_as_lowercase(): void
    {
        $agent = new Agent();
        $agent->last_name = 'DOE';

        $this->assertEquals('doe', $agent->getAttributes()['last_name']);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    
    public function active_scope_returns_only_non_retired_and_web_displayed_agents(): void
    {
        factory(Agent::class)->create(['retired' => false, 'web_display' => true]);
        factory(Agent::class)->create(['retired' => true,  'web_display' => true]);
        factory(Agent::class)->create(['retired' => false, 'web_display' => false]);

        $agents = Agent::active()->get();

        $this->assertCount(1, $agents);
        $this->assertFalse($agents->first()->retired);
        $this->assertTrue($agents->first()->web_display);
    }

    
    public function retired_scope_returns_only_retired_and_web_displayed_agents(): void
    {
        factory(Agent::class)->create(['retired' => true,  'web_display' => true]);
        factory(Agent::class)->create(['retired' => false, 'web_display' => true]);
        factory(Agent::class)->create(['retired' => true,  'web_display' => false]);

        $agents = Agent::retired()->get();

        $this->assertCount(1, $agents);
        $this->assertTrue($agents->first()->retired);
        $this->assertTrue($agents->first()->web_display);
    }

    // -------------------------------------------------------------------------
    // LDAP columns
    // -------------------------------------------------------------------------

    
    public function test_it_returns_onyen_as_ldap_domain_column(): void
    {
        $agent = new Agent();

        $this->assertEquals('onyen', $agent->getLdapDomainColumn());
    }

    
    public function test_it_returns_objectguid_as_ldap_guid_column(): void
    {
        $agent = new Agent();

        $this->assertEquals('objectguid', $agent->getLdapGuidColumn());
    }

    // -------------------------------------------------------------------------
    // deleteImage
    // -------------------------------------------------------------------------

    
    public function delete_image_deletes_file_when_test_it_exists(): void
    {
        File::shouldReceive('exists')->once()->andReturn(true);
        File::shouldReceive('delete')->once();

        $agent = factory(Agent::class)->make(['src' => 'agents/photo.jpg']);
        $agent->deleteImage();
    }

    
    public function delete_image_does_not_call_delete_when_file_does_not_exist(): void
    {
        File::shouldReceive('exists')->once()->andReturn(false);
        File::shouldReceive('delete')->never();

        $agent = factory(Agent::class)->make(['src' => 'agents/photo.jpg']);
        $agent->deleteImage();
    }
}

