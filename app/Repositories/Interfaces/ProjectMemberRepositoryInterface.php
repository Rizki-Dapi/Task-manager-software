<?php

namespace App\Repositories\Interfaces;

use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Database\Eloquent\Collection;

interface ProjectMemberRepositoryInterface
{
    public function findByProjectAndUser(Project $project, int $userId): ?ProjectMember;

    public function isMember(Project $project, int $userId): bool;

    public function listMember(Project $project): Collection;

    public function addMember(Project $project, int $userId): ProjectMember;

    public function removeMember(Project $project, int $userId): void;
}
