<?php

namespace Database\Factories;

use App\Suspect;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuspectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Suspect>
     */
    protected $model = Suspect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'machine' => $this->faker->randomElement(['white', 'peacock', 'green', 'mustard', 'scarlet', 'plum']),
            'profession' => $this->faker->jobTitle(),
            'bio' => $this->faker->paragraph(),
            'quote' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the suspect has the white machine.
     *
     * @return self
     */
    public function white(): self
    {
        return $this->state(fn (array $attributes) => [
            'machine' => 'white',
        ]);
    }

    /**
     * Indicate that the suspect has the peacock machine.
     *
     * @return self
     */
    public function peacock(): self
    {
        return $this->state(fn (array $attributes) => [
            'machine' => 'peacock',
        ]);
    }

    /**
     * Indicate that the suspect has the green machine.
     *
     * @return self
     */
    public function green(): self
    {
        return $this->state(fn (array $attributes) => [
            'machine' => 'green',
        ]);
    }

    /**
     * Indicate that the suspect has the mustard machine.
     *
     * @return self
     */
    public function mustard(): self
    {
        return $this->state(fn (array $attributes) => [
            'machine' => 'mustard',
        ]);
    }

    /**
     * Indicate that the suspect has the scarlet machine.
     *
     * @return self
     */
    public function scarlet(): self
    {
        return $this->state(fn (array $attributes) => [
            'machine' => 'scarlet',
        ]);
    }

    /**
     * Indicate that the suspect has the plum machine.
     *
     * @return self
     */
    public function plum(): self
    {
        return $this->state(fn (array $attributes) => [
            'machine' => 'plum',
        ]);
    }
}

