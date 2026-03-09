<?php

namespace Database\Factories;

use App\Game;
use App\Location;
use App\Quest;
use App\Suspect;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Quest>
     */
    protected $model = Quest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['question', 'minigame']),
            'location_id' => Location::factory(),
            'suspect_id' => Suspect::factory(),
            'game_id' => Game::factory(),
        ];
    }

    /**
     * Indicate that the quest is a question type.
     *
     * @return self
     */
    public function questionType(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'question',
        ]);
    }

    /**
     * Indicate that the quest is a minigame type.
     *
     * @return self
     */
    public function minigameType(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'minigame',
        ]);
    }

    /**
     * Indicate that the quest has no location.
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
     * Indicate that the quest has no suspect.
     *
     * @return self
     */
    public function withoutSuspect(): self
    {
        return $this->state(fn (array $attributes) => [
            'suspect_id' => null,
        ]);
    }
}

