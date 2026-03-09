<?php

namespace Database\Factories;

use App\Player;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Player>
     */
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'onyen' => $this->faker->unique()->userName(),
            'pid' => $this->faker->unique()->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'academic_group_code' => $this->faker->randomElement(['CAS', 'GRAD', 'KFBS', 'SOM', 'SOE']),
            'class_code' => $this->faker->randomElement(['UGRD', 'GRAD', 'MED']),
            'password' => Hash::make('password'),
            'manual' => false,
            'student' => true,
            'checked_in' => false,
        ];
    }

    /**
     * Indicate that the player is a non-student.
     *
     * @return self
     */
    public function nonStudent(): self
    {
        return $this->state(fn (array $attributes) => [
            'academic_group_code' => 'NONS',
            'class_code' => 'NONS',
            'student' => false,
        ]);
    }

    /**
     * Indicate that the player is manually added.
     *
     * @return self
     */
    public function manual(): self
    {
        return $this->state(fn (array $attributes) => [
            'manual' => true,
        ]);
    }

    /**
     * Indicate that the player is checked in.
     *
     * @return self
     */
    public function checkedIn(): self
    {
        return $this->state(fn (array $attributes) => [
            'checked_in' => true,
        ]);
    }

    /**
     * Indicate that the player is an undergraduate.
     *
     * @return self
     */
    public function undergraduate(): self
    {
        return $this->state(fn (array $attributes) => [
            'class_code' => 'UGRD',
            'academic_group_code' => 'CAS',
            'student' => true,
        ]);
    }

    /**
     * Indicate that the player is a graduate student.
     *
     * @return self
     */
    public function graduate(): self
    {
        return $this->state(fn (array $attributes) => [
            'class_code' => 'GRAD',
            'academic_group_code' => 'GRAD',
            'student' => true,
        ]);
    }

    /**
     * Indicate that the player is a medical student.
     *
     * @return self
     */
    public function medical(): self
    {
        return $this->state(fn (array $attributes) => [
            'class_code' => 'MED',
            'academic_group_code' => 'SOM',
            'student' => true,
        ]);
    }

    /**
     * Indicate that the player is a law student.
     *
     * @return self
     */
    public function law(): self
    {
        return $this->state(fn (array $attributes) => [
            'class_code' => 'LAW',
            'academic_group_code' => 'LAW',
            'student' => true,
        ]);
    }

    /**
     * Indicate a specific academic group.
     *
     * @param string $code
     * @return self
     */
    public function inAcademicGroup(string $code): self
    {
        return $this->state(fn (array $attributes) => [
            'academic_group_code' => $code,
        ]);
    }

    /**
     * Indicate a specific class code.
     *
     * @param string $code
     * @return self
     */
    public function withClassCode(string $code): self
    {
        return $this->state(fn (array $attributes) => [
            'class_code' => $code,
        ]);
    }
}

