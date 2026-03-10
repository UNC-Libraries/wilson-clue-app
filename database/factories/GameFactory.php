<?php

namespace Database\Factories;

use App\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Game>
     */
    protected $model = Game::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'suspect_id' => 0,
            'location_id' => 0,
            'evidence_id' => 0,
            'max_teams' => $this->faker->numberBetween(10, 30),
            'winning_team' => 0,
            'start_time' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'end_time' => $this->faker->dateTimeBetween('+2 weeks', '+3 weeks'),
            'registration' => $this->faker->boolean(80),
            'flickr' => $this->faker->url(),
            'flickr_start_img' => $this->faker->imageUrl(),
            'special_thanks' => $this->faker->paragraph(),
            'team_accolades' => $this->faker->paragraph(),
            'archive' => false,
            // Pass a plain PHP array; the 'array' cast on Game handles
            // JSON encoding when the record is persisted to the database.
            'case_file_items' => [],
            'evidence_location_id' => 0,
            'geographic_investigation_location_id' => 0,
            'active' => true,
            'students_only' => false,
        ];
    }

    /**
     * Indicate that the game is archived.
     *
     * @return self
     */
    public function archived(): self
    {
        return $this->state(fn (array $attributes) => [
            'archive' => true,
            'active' => false,
            'start_time' => $this->faker->dateTimeBetween('-6 months', '-3 months'),
            'end_time' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
        ]);
    }

    /**
     * Indicate that the game is in progress.
     *
     * @return self
     */
    public function inProgress(): self
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'end_time' => $this->faker->dateTimeBetween('+1 hour', '+3 hours'),
            'active' => true,
        ]);
    }

    /**
     * Indicate that the game is inactive.
     *
     * @return self
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the game is for students only.
     *
     * @return self
     */
    public function studentsOnly(): self
    {
        return $this->state(fn (array $attributes) => [
            'students_only' => true,
        ]);
    }

    /**
     * Indicate that the game has a winning team.
     *
     * @param int $teamId
     * @return self
     */
    public function withWinner(int $teamId): self
    {
        return $this->state(fn (array $attributes) => [
            'winning_team' => $teamId,
        ]);
    }

    /**
     * Indicate that registration is closed.
     *
     * @return self
     */
    public function registrationClosed(): self
    {
        return $this->state(fn (array $attributes) => [
            'registration' => false,
        ]);
    }

    /**
     * Indicate that the game has a complete solution set.
     *
     * @return self
     */
    public function withSolution(): self
    {
        return $this->state(fn (array $attributes) => [
            'suspect_id' => \App\Suspect::factory(),
            'location_id' => \App\Location::factory(),
            'evidence_id' => \App\Evidence::factory(),
        ]);
    }

    /**
     * Indicate that the game has an evidence location configured.
     *
     * @return self
     */
    public function withEvidenceLocation(): self
    {
        return $this->state(fn (array $attributes) => [
            'evidence_location_id' => \App\Location::factory(),
        ]);
    }

    /**
     * Indicate that the game has a geographic investigation location configured.
     *
     * @return self
     */
    public function withGeographicInvestigationLocation(): self
    {
        return $this->state(fn (array $attributes) => [
            'geographic_investigation_location_id' => \App\Location::factory(),
        ]);
    }
}

