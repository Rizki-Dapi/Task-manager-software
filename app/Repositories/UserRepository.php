<?php

namespace App\Repositories;

use App\Enums\RoleUserEnum;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Override;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByName(string $name): ?User
    {
        return User::where('name', $name)->first();
    }

    #[Override]
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when(
                $search,
                fn ($query, $search) => $query->where('name', 'ilike', "%{$search}%")
            )
            ->latest()
            ->paginate($perPage);
    }

    public function countByRole(string $role): int
    {
        $roleModel = Role::where('name', $role)->first();

        return $roleModel ? $roleModel->user()->count() : 0;
    }

    public function hasAssignedTask(User $user): bool
    {
        return $user->assignedTask()->exists();
    }

    #[Override]
    public function paginateByRole(string $role, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->where('name', $role))
            ->when($search, fn ($query, $search) => $query->where(
                fn ($q) => $q->where('name', 'ilike', "%{$search}")
            ))
            ->latest()
            ->paginate($perPage);
    }

    #[Override]
    public function getMembers(): Collection
    {
        return User::query()
            ->whereHas(
                'roles',
                fn ($query) => $query->where('name', RoleUserEnum::MEMBER->value)
            )
            ->get();
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update(array_intersect_key($data, array_flip(['name', 'email', 'password'])));

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
