<?php

namespace App\Services;

use App\Enums\RoleUserEnum;
use App\Enums\StatusEnum;
use App\Exceptions\AssigneeNotProjectMemberException;
use App\Exceptions\ProjectDeadlinePassException;
use App\Exceptions\TaskAlreadyAssignedException;
use App\Exceptions\TaskNotAssignedException;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\ProjectMemberRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectMemberRepositoryInterface $projectMemberRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly LogRepositoryInterface $logRepository,
    ) {}

    public function findById(int $taskId, User $actingUser): Task
    {
        $task = $this->taskRepository->findById($taskId);

        if (! $task) {
            throw new ModelNotFoundException('task not found');
        }

        $project = $task->projects;

        $canView = match (true) {
            $actingUser->hasRole(RoleUserEnum::SUPER_ADMIN->value) => true,
            $actingUser->hasRole(RoleUserEnum::ADMIN->value) => $project->created_by === $actingUser->id,
            default => $this->projectMemberRepository->isMember($task->project_id, $actingUser->id),
        };

        if (! $canView) {
            throw new AssigneeNotProjectMemberException(
                'Cannot access this task, because your not a memberr or creator in this project. '
            );
        }

        return $task;
    }

    public function getAndFilter(int $projectId, User $actingUser, array $filters): Collection
    {
        return $this->taskRepository->getAndFilter($projectId, $actingUser, $filters);
    }

    public function create(array $data): Task
    {
        $data['status'] ??= StatusEnum::IN_PROGRESS;

        $task = $this->taskRepository->create($data);

        $this->syncProjectStatus($task->project_id);

        $this->logRepository->record([
            'action' => 'admin.create_task',
            'context' => ['task_id' => $task->id],
        ]);

        return $task->load(['assigned', 'labels', 'projects']);
    }

    public function update(Task $task, array $data): Task
    {
        $updated = $this->taskRepository->update($task, $data);

        $this->logRepository->record([
            'action' => 'admin.upadted_task',
            'context' => ['task_id' => $task->id, 'fields' => array_keys($data)],
        ]);

        return $updated->refresh()->load(['assigned', 'labels', 'projects']);
    }

    public function assignTask(Task $task, User $actingUser): Task
    {
        $project = $this->projectRepository->findById($task->project_id);

        if (! $this->projectMemberRepository->isMember(
            $project,
            $actingUser->id,
        )) {
            throw new AssigneeNotProjectMemberException();
        }

        if ($this->taskRepository->hasAssigneeTask($task)) {
            throw new TaskAlreadyAssignedException();
        }

        $project = $this->projectRepository->findById($task->project_id);

        if ($project->due_date && now()->startOfDay()->greaterThan($project->due_date)) {
            throw new ProjectDeadlinePassException();
        }

        $taskAssign = $this->taskRepository->update($task, [
            'assignee_id' => $actingUser->id,
            'status' => StatusEnum::COMPLETED,
        ]);

        $this->syncProjectStatus($task->project_id);

        $this->logRepository->record([
            'action' => 'member.Assign_task',
            'context' => ['task_id' => $task->id],
        ]);

        return $taskAssign->refresh()->load(['assigned', 'labels', 'projects']);
    }

    public function countByLabel(): Collection
    {
        return $this->taskRepository->countByLabel();
    }

    public function unAssign(Task $task): Task
    {
        if (is_null($task->assignee_id)) {
            throw new TaskNotAssignedException('This task has no assignee to remove. ');
        }

        $taskUnAssign = $this->taskRepository->update($task, [
            'assignee_id' => null,
            'status' => 'in-progress',
        ]);

        $this->syncProjectStatus($task->project_id);

        $this->logRepository->record([
            'action' => 'admin.unassign_task',
            'context' => ['task_id' => $task->id],
        ]);

        return $taskUnAssign->refresh()->load(['assigned', 'labels', 'projects']);
    }

    public function delete(Task $task): void
    {
        if ($this->taskRepository->hasAssigneeTask($task)) {
            throw new TaskAlreadyAssignedException(
                'Cannot delete task, because it still has assignee, unassign the task first. '
            );
        }

        $this->taskRepository->delete($task);

        $this->syncProjectStatus($task->project_id);

        $this->logRepository->record([
            'action' => 'admin.delete_task',
            'context' => ['task_id' => $task->id],
        ]);
    }

    private function syncProjectStatus(int $projectId): void
    {
        $totalTask = $this->taskRepository->countByProjectId($projectId);

        $completedTask = $this->taskRepository->countCompletedByProjectId($projectId);

        $project = $this->projectRepository->findById($projectId);

        $status = ($totalTask > 0 && $totalTask === $completedTask)
            ? StatusEnum::COMPLETED
            : StatusEnum::IN_PROGRESS;

        $this->projectRepository->update($project, ['status' => $status]);
    }
}
