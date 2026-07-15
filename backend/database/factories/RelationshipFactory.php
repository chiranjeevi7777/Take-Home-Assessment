<?php

namespace Database\Factories;

use App\Models\Relationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationshipFactory extends Factory
{
    protected $model = Relationship::class;

    public function definition(): array
    {
        return [
            'follower_id' => User::factory(),
            'following_id' => User::factory(),
            'strength' => $this->faker->randomFloat(2, 1.0, 5.0),
        ];
    }

    /**
     * State: strong relationship (high interaction history).
     */
    public function strong(): static
    {
        return $this->state(fn () => [
            'strength' => $this->faker->randomFloat(2, 7.0, 10.0),
        ]);
    }

    /**
     * State: weak relationship (new or infrequent interaction).
     */
    public function weak(): static
    {
        return $this->state(fn () => [
            'strength' => $this->faker->randomFloat(2, 0.5, 2.0),
        ]);
    }
}
