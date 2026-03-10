<?php

namespace Database\Factories;

use App\MinigameImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinigameImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MinigameImage>
     */
    protected $model = MinigameImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'year' => $this->faker->year(),
            'src' => 'minigames/' . $this->faker->slug() . '.jpg',
        ];
    }

    /**
     * Indicate a specific year.
     *
     * @param int $year
     * @return self
     */
    public function forYear(int $year): self
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }
}

