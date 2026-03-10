<?php

namespace Database\Factories;

use App\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Location>
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'floor' => $this->faker->numberBetween(1, 5),
           // 'description' => $this->faker->paragraph(),
            'map_section' => $this->faker->randomElement(['north', 'south', 'east', 'west', 'center']),
        ];
    }

    /**
     * Indicate that the location is on a specific floor.
     *
     * @param int $floor
     * @return self
     */
    public function onFloor(int $floor): self
    {
        return $this->state(fn (array $attributes) => [
            'floor' => $floor,
        ]);
    }

    /**
     * Indicate that the location is in a specific map section.
     *
     * @param string $section
     * @return self
     */
    public function inMapSection(string $section): self
    {
        return $this->state(fn (array $attributes) => [
            'map_section' => $section,
        ]);
    }
}

