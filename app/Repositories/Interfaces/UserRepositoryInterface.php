<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByName(string $name): ?User;

    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function getMembers(): Collection;

    public function countByRole(string $role): int;

    public function hasAssignedTask(User $user): bool;

    public function paginateByRole(string $role, ?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): void;
}
