<?php

namespace Database\Factories;

use App\Location;
use App\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Question>
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => $this->faker->sentence() . '?',
            'type' => false,
            'full_answer' => $this->faker->sentence(),
            'src' => 'questions/' . $this->faker->slug() . '.jpg',
            'location_id' => 0,
        ];
    }

    /**
     * Indicate that the question is of type true.
     *
     * @return self
     */
    public function typeTrue(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => true,
        ]);
    }

    /**
     * Indicate that the question has no image.
     *
     * @return self
     */
    public function withoutImage(): self
    {
        return $this->state(fn (array $attributes) => [
            'src' => null,
        ]);
    }

    /**
     * Indicate that the question has no location.
     *
     * @return self
     */
    public function withoutLocation(): self
    {
        return $this->state(fn (array $attributes) => [
            'location_id' => null,
        ]);
    }

    /**
     * Attach this question to a location.
     *
     * @return self
     */
    public function withLocation(): self
    {
        return $this->state(fn (array $attributes) => [
            'location_id' => Location::factory(),
        ]);
    }
}
