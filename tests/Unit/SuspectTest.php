<?php

namespace Tests\Unit;

use App\Quest;
use App\Suspect;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspectTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable / Appends
    // -------------------------------------------------------------------------

    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $suspect = new Suspect();

        $this->assertEquals([
            'name',
            'machine',
            'profession',
            'bio',
            'quote',
        ], $suspect->getFillable());
    }

    public function test_it_appends_bootstrap_color_attribute(): void
    {
        $suspect = new Suspect();

        $this->assertEquals(['bootstrap_color'], $suspect->getAppends());
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_it_has_a_quest_has_many_relationship(): void
    {
        $suspect = new Suspect();

        $this->assertInstanceOf(HasMany::class, $suspect->quest());
    }

    public function quest_relationship_uses_expected_related_model_and_foreign_key(): void
    {
        $suspect = new Suspect();

        $this->assertInstanceOf(Quest::class, $suspect->quest()->getRelated());
        $this->assertEquals('suspect_id', $suspect->quest()->getForeignKeyName());
    }

    public function test_it_loads_related_quests_for_a_suspect(): void
    {
        $suspect = Suspect::factory()->create();
        Quest::factory()->count(2)->create(['suspect_id' => $suspect->id]);

        $this->assertCount(2, $suspect->quest);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function test_bootstrap_color_maps_machine_to_expected_class(): void
    {
        $expectations = [
            'white' => 'dark',
            'peacock' => 'primary',
            'green' => 'success',
            'mustard' => 'warning',
            'scarlet' => 'danger',
            'plum' => 'info',
        ];

        foreach ($expectations as $machine => $color) {
            $suspect = Suspect::factory()->make(['machine' => $machine]);
            $this->assertEquals($color, $suspect->bootstrap_color);
        }
    }

    public function test_side_returns_right_page_for_plum_mustard_and_green(): void
    {
        foreach (['plum', 'mustard', 'green'] as $machine) {
            $suspect = Suspect::factory()->make(['machine' => $machine]);
            $this->assertEquals('char-right-page', $suspect->side);
        }
    }

    public function test_side_returns_left_page_for_other_machines(): void
    {
        foreach (['white', 'peacock', 'scarlet'] as $machine) {
            $suspect = Suspect::factory()->make(['machine' => $machine]);
            $this->assertEquals('char-left-page', $suspect->side);
        }
    }

    public function test_image_path_returns_expected_base_path(): void
    {
        $suspect = Suspect::factory()->make();

        $this->assertEquals('images/suspects/', $suspect->image_path);
    }

    public function test_face_card_tiny_and_logo_image_accessors_use_machine_name(): void
    {
        $suspect = Suspect::factory()->make(['machine' => 'scarlet']);

        $this->assertEquals('images/suspects/scarlet_face.jpg', $suspect->face_image);
        $this->assertEquals('images/suspects/scarlet_card.jpg', $suspect->card_image);
        $this->assertEquals('images/suspects/scarlet_tiny.jpg', $suspect->tiny_image);
        $this->assertEquals('images/suspects/scarlet_logo.jpg', $suspect->logo);
    }
}

