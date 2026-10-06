<?php

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Exceptions\UserAlreadyProjectMemberException;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\ProjectMemberRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectMemberService
{
    public function __construct(
        private readonly ProjectMemberRepositoryInterface $projectMemberRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LogRepositoryInterface $logRepository,
    ) {}

    public function findByProjectAndUser(Project $project, int $userId): ?ProjectMember
    {
        $data = $this->projectMemberRepository->findByProjectAndUser($project, $userId);

        if (! $data) {
            throw new ModelNotFoundException('Project member not found');
        }

        return $data->load(['user', 'projects']);
    }

    public function listMember(Project $project): Collection
    {
        return $this->projectMemberRepository->listMember($project);
    }

    public function addMember(Project $project, int $userId): ProjectMember
    {
        if ($this->projectMemberRepository->isMember($project, $userId)) {
            throw new UserAlreadyProjectMemberException();
        }

        $data = $this->projectMemberRepository->addMember($project, $userId);

        $this->logRepository->record([
            'action' => 'admin.add_member_to_project',
            'context' => [
                'project_id' => $project->id,
                'user_id' => $userId,
            ]
        ]);

        return $data->load(['user', 'projects']);
    }

    public function removeMember(Project $project, int $userId): void
    {
        $member = $this->projectMemberRepository->findByProjectAndUser($project, $userId);

        if (!$member) {
            throw new ModelNotFoundException('Project member not found');
        }

        $user = $this->userRepository->findById($userId);

        if ($this->userRepository->hasAssignedTask($user)) {
            throw new ResourceInUseException(
                'Cannot remove this member because it still has assigned id in task, Unassigned the task first. '
            );
        }

        $this->projectMemberRepository->removeMember($project, $userId);

        $this->logRepository->record([
            'action' => 'admin.remove_member_from_project',
            'context' => [
                'project_id' => $project->id,
                'user_id' => $userId,
            ]
        ]);
    }
}
