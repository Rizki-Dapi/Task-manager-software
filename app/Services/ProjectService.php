<?php

namespace App\Services;

use App\Enums\RoleUserEnum;
use App\Enums\StatusEnum;
use App\Exceptions\ResourceInUseException;
use App\Exceptions\UserNotProjectMemberException;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\ProjectMemberRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectMemberRepositoryInterface $projectMemberRespository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly LogRepositoryInterface $logRepository,
    ) {}

    public function findById(int $projectId, User $actingUser): Project
    {
        $project = $this->projectRepository->findById($projectId);

        if (! $project) {
            throw new ModelNotFoundException('project not found');
        }

        $role = $actingUser->roles?->name;

        if ($role === RoleUserEnum::SUPER_ADMIN->value) {
            $canView = true;
        } elseif ($role === RoleUserEnum::ADMIN->value) {
            $canView = $project->created_by === $actingUser->id;
        } elseif ($role === RoleUserEnum::MEMBER->value) {
            $canView = $this->projectMemberRespository->isMember(
                $project,
                $actingUser->id
            );
        } else {
            $canView = false;
        }

        if (! $canView) {
            throw new UserNotProjectMemberException(
                'You do not have access to this project.'
            );
        }

        return $project;
    }

    public function paginateAll(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->projectRepository->paginateAll($filters, $perPage);
    }

    public function paginateByCreator(int $actingUser, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->projectRepository->paginateByCreator($actingUser, $filters, $perPage);
    }

    public function paginateByMember(int $actingUser, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->projectRepository->paginateByMember($actingUser, $filters, $perPage);
    }

    public function create(array $data, User $actingUser): Project
    {
        $data['status'] ??= StatusEnum::IN_PROGRESS;

        $project = $this->projectRepository->create([
            ...$data,
            'created_by' => $actingUser->id,
        ]);

        $this->logRepository->record([
            'action' => 'admin.create_project',
            'context' => ['project_id' => $project->id],
        ]);

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $updated = $this->projectRepository->update($project, $data);

        $this->logRepository->record([
            'action' => 'admin.updated_project',
            'context' => ['project_id' => $updated->id, 'fields' => array_keys($data)],
        ]);

        return $updated;
    }

    public function delete(Project $project): void
    {
        if ($this->taskRepository->hasAssigneeTaskByProjectId($project)) {
            throw new ResourceInUseException(
                'Cannot delete this project, because it still has assigned task, need unassigned the task first. '
            );
        }

        $this->projectRepository->delete($project);

        $this->logRepository->record([
            'action' => 'admin.delete_project',
            'context' => ['project_id' => $project->id],
        ]);
    }
}
