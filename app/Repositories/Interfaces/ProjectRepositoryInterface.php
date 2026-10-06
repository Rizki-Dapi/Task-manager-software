<?php

namespace App\Repositories\Interfaces;

use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function findById(int $id): ?Project;

    public function paginateAll(
        array $filters = [],
        int $perPage = 10
    ): LengthAwarePaginator;

    public function paginateByCreator(
        int $userId,
        array $filters = [],
        int $perPage = 10
    ): LengthAwarePaginator;

    public function paginateByMember(
        int $userId,
        array $filters = [],
        int $perPage = 10
    ): LengthAwarePaginator;

    public function create(array $data): Project;

    public function update(Project $project, array $data): Project;

    public function delete(Project $project): void;
}
