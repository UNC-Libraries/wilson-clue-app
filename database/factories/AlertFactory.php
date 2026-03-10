<?php

namespace Database\Factories;

use App\Alert;
use App\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Alert>
     */
    protected $model = Alert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => 0,
            'message' => $this->faker->sentence(),
        ];
    }

    /**
     * Attach this alert to a game.
     *
     * @return self
     */
    public function withGame(): self
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => Game::factory(),
        ]);
    }
}

