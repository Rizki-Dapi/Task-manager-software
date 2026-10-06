<?php

namespace Database\Factories;

use App\Enums\RoleUserEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => fn () => Role::firstOrCreate(['name' => RoleUserEnum::MEMBER->value])->id,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(RoleUserEnum $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::firstOrCreate(['name' => $role->value])->id,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->withRole(RoleUserEnum::SUPER_ADMIN);
    }

    public function admin(): static
    {
        return $this->withRole(RoleUserEnum::ADMIN);
    }

    public function member(): static
    {
        return $this->withRole(RoleUserEnum::MEMBER);
    }
}
