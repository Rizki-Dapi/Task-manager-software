<?php

namespace Database\Factories;

use App\Enums\RoleUserEnum;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => RoleUserEnum::MEMBER->value,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn() => ['name' => RoleUserEnum::SUPER_ADMIN->value]);
    }

    public function admin(): static
    {
        return $this->state(fn() => ['name' => RoleUserEnum::ADMIN->value]);
    }

    public function member(): static
    {
        return $this->state(fn() => ['name' => RoleUserEnum::MEMBER->value]);
    }
}
