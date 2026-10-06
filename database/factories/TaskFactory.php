<?php

namespace Database\Factories;

use App\Enums\StatusEnum;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => StatusEnum::IN_PROGRESS->value,
            'assignee_id' => null,
            'label_id' => Label::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['statut', StatusEnum::COMPLETED->value]);
    }

    public function assignedTo(User|int $user): static
    {
        return $this->state(fn () => [
            'assignee_id' => $user instanceof User ? $user->id : $user,
            'status' => StatusEnum::COMPLETED->value,
        ]);
    }
}
