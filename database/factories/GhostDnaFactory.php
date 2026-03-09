<?php

namespace Database\Factories;

use App\GhostDna;
use Illuminate\Database\Eloquent\Factories\Factory;

class GhostDnaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<GhostDna>
     */
    protected $model = GhostDna::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sequence' => $this->faker->numberBetween(1, 100),
            'pair' => $this->faker->randomElement(['A-T', 'T-A', 'C-G', 'G-C']),
        ];
    }

    /**
     * Indicate a specific sequence number.
     *
     * @param int $sequence
     * @return self
     */
    public function withSequence(int $sequence): self
    {
        return $this->state(fn (array $attributes) => [
            'sequence' => $sequence,
        ]);
    }

    /**
     * Indicate a specific DNA pair.
     *
     * @param string $pair
     * @return self
     */
    public function withPair(string $pair): self
    {
        return $this->state(fn (array $attributes) => [
            'pair' => $pair,
        ]);
    }
}

