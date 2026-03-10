<?php

namespace Database\Factories;

use App\Evidence;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvidenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Evidence>
     */
    protected $model = Evidence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'src' => 'evidence/' . $this->faker->slug() . '.jpg',
        ];
    }
}

