<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Repositories\Interfaces\ProjectMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Override;

class ProjectMemberRepository implements ProjectMemberRepositoryInterface
{
    #[Override]
    public function findByProjectAndUser(Project $project, int $userId): ?ProjectMember
    {
        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->first();
    }

    public function isMember(Project $project, int $userId): bool
    {
        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->exists();
    }

    #[Override]
    public function listMember(Project $project): Collection
    {
        return ProjectMember::with('user')
            ->where('project_id', $project->id)
            ->get();
    }

    #[Override]
    public function addMember(Project $project, int $userId): ProjectMember
    {
        return ProjectMember::firstOrCreate([
            'project_id' => $project->id,
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function removeMember(Project $project, int $userId): void
    {
        ProjectMember::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->delete();
    }
}
