<?php

namespace Tests\Unit;

use App\Answer;
use App\Question;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Fillable
    // -------------------------------------------------------------------------

    
    public function test_it_has_the_correct_fillable_attributes(): void
    {
        $answer = new Answer();

        $this->assertEquals(['question_id', 'text'], $answer->getFillable());
    }

    
    public function test_it_can_be_mass_assigned_question_id_and_text(): void
    {
        $question = Question::factory()->create();

        $answer = new Answer([
            'question_id' => $question->id,
            'text'        => 'Test answer',
        ]);

        $this->assertEquals($question->id, $answer->question_id);
        $this->assertEquals('Test answer', $answer->text);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    
    public function test_it_has_a_question_belongs_to_relationship(): void
    {
        $answer = new Answer();

        $this->assertInstanceOf(BelongsTo::class, $answer->question());
    }

    
    public function test_it_belongs_to_a_question(): void
    {
        $question = Question::factory()->create();
        $answer   = Answer::factory()->create(['question_id' => $question->id]);

        $this->assertInstanceOf(Question::class, $answer->question);
        $this->assertEquals($question->id, $answer->question->id);
    }

    
    public function question_relationship_uses_correct_foreign_key(): void
    {
        $answer = new Answer();

        $this->assertEquals('question_id', $answer->question()->getForeignKeyName());
    }

    
    public function question_relationship_uses_correct_related_model(): void
    {
        $answer = new Answer();

        $this->assertInstanceOf(Question::class, $answer->question()->getRelated());
    }

    // -------------------------------------------------------------------------
    // Database
    // -------------------------------------------------------------------------

    
    public function test_it_can_be_created_in_the_database(): void
    {
        $question = Question::factory()->create();
        $answer   = Answer::factory()->create([
            'question_id' => $question->id,
            'text'        => 'Test answer text',
        ]);

        $this->assertDatabaseHas('answers', [
            'id'          => $answer->id,
            'question_id' => $question->id,
            'text'        => 'Test answer text',
        ]);
    }

    public function test_it_can_be_deleted_from_the_database(): void
    {
        $question = Question::factory()->create();
        $answer   = Answer::factory()->create(['question_id' => $question->id]);

        $answer->delete();

        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    public function test_it_can_be_updated_in_the_database(): void
    {
        $question = Question::factory()->create();
        $answer   = Answer::factory()->create([
            'question_id' => $question->id,
            'text'        => 'Original text',
        ]);

        $answer->update(['text' => 'Updated text']);

        $this->assertDatabaseHas('answers', [
            'id'   => $answer->id,
            'text' => 'Updated text',
        ]);
    }

    public function multiple_answers_can_belong_to_the_same_question(): void
    {
        $question = Question::factory()->create();
        Answer::factory()->count(3)->create(['question_id' => $question->id]);

        $this->assertCount(3, $question->answers);
    }

    public function deleting_a_question_does_not_automatically_delete_its_answers(): void
    {
        $question = Question::factory()->create();
        $answer   = Answer::factory()->create(['question_id' => $question->id]);

        $question->delete();

        $this->assertDatabaseHas('answers', ['id' => $answer->id]);
    }
}

