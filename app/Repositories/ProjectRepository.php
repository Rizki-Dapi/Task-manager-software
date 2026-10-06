<?php

namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function findById(int $id): ?Project
    {
        return Project::find($id);
    }

    #[Override]
    public function paginateAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuary($filters)->paginate($perPage);
    }

    #[Override]
    public function paginateByCreator(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuary($filters)
            ->where('created_by', $userId)
            ->paginate($perPage);
    }

    #[Override]
    public function paginateByMember(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuary($filters)
            ->whereHas(
                'projectMembers',
                fn ($query) => $query->where('user_id', $userId)
            )->paginate($perPage);
    }

    #[Override]
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    #[Override]
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    #[Override]
    public function delete(Project $project): void
    {
        $project->delete();
    }

    private function baseQuary(array $filters): Builder
    {
        return Project::query()
            ->with('creator')
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->where('name', 'ilike', "%{$search}%")
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )->latest();
    }
}
