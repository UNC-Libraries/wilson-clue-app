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
            'location_id' => 0,
            'suspect_id' => 0,
            'game_id' => 0,
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

    /**
     * Attach this quest to a game.
     *
     * @return self
     */
    public function withGame(): self
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => Game::factory(),
        ]);
    }

    /**
     * Attach this quest to a location.
     *
     * @return self
     */
    public function withLocation(): self
    {
        return $this->state(fn (array $attributes) => [
            'location_id' => Location::factory(),
        ]);
    }

    /**
     * Attach this quest to a suspect.
     *
     * @return self
     */
    public function withSuspect(): self
    {
        return $this->state(fn (array $attributes) => [
            'suspect_id' => Suspect::factory(),
        ]);
    }
}
