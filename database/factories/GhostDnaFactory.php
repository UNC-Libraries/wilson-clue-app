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
            'sequence' => $this->faker->regexify('[ghst]{6}'),
            'pair' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate a specific sequence.
     *
     * @param string $sequence
     * @return self
     */
    public function withSequence(string $sequence): self
    {
        return $this->state(fn (array $attributes) => [
            'sequence' => $sequence,
        ]);
    }

    /**
     * Indicate a specific DNA pair number.
     *
     * @param int $pair
     * @return self
     */
    public function withPair(int $pair): self
    {
        return $this->state(fn (array $attributes) => [
            'pair' => $pair,
        ]);
    }
}
