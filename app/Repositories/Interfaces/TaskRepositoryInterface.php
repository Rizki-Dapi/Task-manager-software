<?php

namespace App\Repositories\Interfaces;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?Task;

    public function getAndFilter(int $projectId, User $user, array $filters = []): Collection;

    public function countByLabel(): Collection;

    public function countByProjectId(int $projectId): int;

    public function countCompletedByProjectId(int $projectId): int;

    public function hasAssigneeTask(Task $task): bool;

    public function hasAssigneeTaskByProjectId(Project $projectId): bool;

    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): void;
}
