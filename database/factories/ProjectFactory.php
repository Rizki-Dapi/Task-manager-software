<?php

namespace Database\Factories;

use App\Enums\StatusEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => StatusEnum::COMPLETED->value,
            'due_date' => now()->addWeeks(2)->toDateString(),
            'created_by' => User::factory()->admin(),
        ];
    }

    public function complete(): static
    {
        return $this->state(fn() => ['status' => StatusEnum::COMPLETED->value]);
    }

    public function withoutDueDate(): static
    {
        return $this->state(fn() => ['due_date' => null]);
    }

    public function overdue(): static
    {
        return $this->state(fn() => ['due_date' => now()->subDay()->toDateString()]);
    }

    public function dueToday(): static
    {
        return $this->state(fn() => ['due_date' => now()->toDateString()]);
    }
}
