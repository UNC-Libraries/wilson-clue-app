<?php

namespace Database\Factories;

use App\IncorrectAnswer;
use App\Question;
use App\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncorrectAnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<IncorrectAnswer>
     */
    protected $model = IncorrectAnswer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'question_id' => Question::factory(),
            'answer' => $this->faker->sentence(),
            'judged' => false,
        ];
    }

    /**
     * Indicate that the incorrect answer has been judged.
     *
     * @return self
     */
    public function judged(): self
    {
        return $this->state(fn (array $attributes) => [
            'judged' => true,
        ]);
    }

    /**
     * Indicate that the incorrect answer has not been judged.
     *
     * @return self
     */
    public function notJudged(): self
    {
        return $this->state(fn (array $attributes) => [
            'judged' => false,
        ]);
    }
}

