<?php

namespace Database\Factories;

use App\Answer;
use App\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Answer>
     */
    protected $model = Answer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => 0,
            'text' => $this->faker->sentence(),
        ];
    }

    /**
     * Attach this answer to a question.
     *
     * @return self
     */
    public function withQuestion(): self
    {
        return $this->state(fn (array $attributes) => [
            'question_id' => Question::factory(),
        ]);
    }
}
