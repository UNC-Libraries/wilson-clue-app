<?php

namespace Database\Factories;

use App\Evidence;
use App\Game;
use App\Location;
use App\Suspect;
use App\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Team>
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'dietary' => $this->faker->optional()->sentence(),
            'bonus_points' => 0,
            'game_id' => Game::factory(),
            'suspect_id' => null,
            'location_id' => null,
            'evidence_id' => null,
            'indictment_time' => null,
            'evidence_selected_at' => null,
            'waitlist' => false,
            'score' => 0.0,
        ];
    }

    /**
     * Indicate that the team is on the waitlist.
     *
     * @return self
     */
    public function waitlist(): self
    {
        return $this->state(fn (array $attributes) => [
            'waitlist' => true,
        ]);
    }

    /**
     * Indicate that the team is registered (not on waitlist).
     *
     * @return self
     */
    public function registered(): self
    {
        return $this->state(fn (array $attributes) => [
            'waitlist' => false,
        ]);
    }

    /**
     * Indicate that the team has made an indictment.
     *
     * @return self
     */
    public function withIndictment(): self
    {
        return $this->state(fn (array $attributes) => [
            'suspect_id' => Suspect::factory(),
            'location_id' => Location::factory(),
            'evidence_id' => Evidence::factory(),
            'indictment_time' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Indicate that the team has selected evidence.
     *
     * @return self
     */
    public function withEvidenceSelected(): self
    {
        return $this->state(fn (array $attributes) => [
            'evidence_id' => Evidence::factory(),
            'evidence_selected_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
        ]);
    }

    /**
     * Indicate that the team has bonus points.
     *
     * @param int $points
     * @return self
     */
    public function withBonusPoints(int $points = 10): self
    {
        return $this->state(fn (array $attributes) => [
            'bonus_points' => $points,
        ]);
    }

    /**
     * Indicate that the team has a specific score.
     *
     * @param float $score
     * @return self
     */
    public function withScore(float $score): self
    {
        return $this->state(fn (array $attributes) => [
            'score' => $score,
        ]);
    }

    /**
     * Indicate that the team has dietary restrictions.
     *
     * @param string|null $dietary
     * @return self
     */
    public function withDietary(?string $dietary = null): self
    {
        return $this->state(fn (array $attributes) => [
            'dietary' => $dietary ?? $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the team has a correct indictment.
     *
     * @return self
     */
    public function withCorrectIndictment(): self
    {
        return $this->afterCreating(function (Team $team) {
            $game = $team->game;

            $team->update([
                'suspect_id' => $game->suspect_id,
                'location_id' => $game->location_id,
                'evidence_id' => $game->evidence_id,
                'indictment_time' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            ]);
        });
    }
}

