<?php

namespace Database\Factories;

use App\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Agent>
     */
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'onyen' => $this->faker->unique()->userName(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'job_title' => $this->faker->jobTitle(),
            'title' => $this->faker->title(),
            'location' => $this->faker->city(),
            'retired' => false,
            'web_display' => true,
            'admin' => false,
            'src' => 'agents/' . $this->faker->slug() . '.jpg',
        ];
    }

    /**
     * Indicate that the agent is retired.
     *
     * @return self
     */
    public function retired(): self
    {
        return $this->state(fn (array $attributes) => [
            'retired' => true,
        ]);
    }

    /**
     * Indicate that the agent is not displayed on the web.
     *
     * @return self
     */
    public function notWebDisplay(): self
    {
        return $this->state(fn (array $attributes) => [
            'web_display' => false,
        ]);
    }

    /**
     * Indicate that the agent is an admin.
     *
     * @return self
     */
    public function admin(): self
    {
        return $this->state(fn (array $attributes) => [
            'admin' => true,
        ]);
    }
}

