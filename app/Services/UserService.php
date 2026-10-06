<?php

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly LogRepositoryInterface $logRepositeory,
    ) {}

    public function findById(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (! $user) {
            throw new ModelNotFoundException('user not found');
        }

        return $user;
    }

    public function findByName(string $name): User
    {
        $user = $this->userRepository->findByName($name);

        if (! $user) {
            throw new ModelNotFoundException('user not found');
        }

        return $user;
    }

    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->paginate($search, $perPage);
    }

    public function getMembers(): Collection
    {
        return $this->userRepository->getMembers();
    }

    public function countByRole(string $role): int
    {
        return $this->userRepository->countByRole($role);
    }

    public function paginateByRole(string $role, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->paginateByRole($role, $search, $perPage);
    }

    public function create(array $data): User
    {
        $user = $this->userRepository->create($data);

        $this->logRepositeory->record([
            'action' => 'super_admin.user(member)_create',
            'context' => ['user_id' => $user->id],
        ]);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $updated = $this->userRepository->update($user, $data);

        $this->logRepositeory->record([
            'action' => 'super_admin.user(member)_update',
            'context' => ['user_id' => $updated->id, 'field' => array_keys($data)],
        ]);

        return $updated;
    }

    public function delete(User $user): void
    {
        if ($this->userRepository->hasAssignedTask($user)) {
            throw new ResourceInUseException(
                'Cannot delete this user because it still has assigned id in task, Unassigned the task first. '
            );
        }

        $this->userRepository->delete($user);

        $this->logRepositeory->record([
            'action' => 'super_admin.user(member)_delete',
            'context' => ['user_id' => $user->id],
        ]);
    }
}
