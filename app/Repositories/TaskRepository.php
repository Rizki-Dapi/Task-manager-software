<?php

namespace App\Repositories;

use App\Enums\StatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Override;

class TaskRepository implements TaskRepositoryInterface
{
    #[Override]
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    #[Override]
    public function getAndFilter(int $projectId, User $user, array $filters = []): Collection
    {
        return Task::query()
            ->where('project_id', $projectId)
            ->where(
                fn($query) => $query
                    ->whereHas(
                        'projects',
                        fn($query) => $query->where('created_by', $user->id)
                    )
                    ->orWhereHas(
                        'projects.projectMembers',
                        fn($query) => $query->where('user_id', $user->id)
                    )
            )
            ->when(
                $filters['status'] ?? null,
                fn($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['search'] ?? null,
                fn($query, $search) => $query->where('title', 'ilike', "%{$search}%")
            )->get();
    }

    #[Override]
    public function countByLabel(): Collection
    {
        return Task::query()
            ->join('labels', 'labels.id', '=', 'tasks.label_id')
            ->selectRaw('labels.name, COUNT(tasks.id) as total')
            ->groupBy('labels.name')
            ->orderByDesc('total')
            ->get();
    }

    #[Override]
    public function countByProjectId(int $projectId): int
    {
        return Task::query()
            ->where('project_id', $projectId)
            ->count();
    }

    #[Override]
    public function countCompletedByProjectId(int $projectId): int
    {
        return Task::query()
            ->where('project_id', $projectId)
            ->where('status', StatusEnum::COMPLETED)
            ->count();
    }

    #[Override]
    public function hasAssigneeTask(Task $task): bool
    {
        return $task->assigned()->exists();
    }

    #[Override]
    public function hasAssigneeTaskByProjectId(Project $project): bool
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->whereNotNull('assignee_id')
            ->exists();
    }

    #[Override]
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    #[Override]
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
