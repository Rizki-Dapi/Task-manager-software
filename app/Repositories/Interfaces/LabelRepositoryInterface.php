<?php

namespace App\Repositories\Interfaces;

use App\Models\Label;
use Illuminate\Database\Eloquent\Collection;

interface LabelRepositoryInterface
{
    public function get(): Collection;

    public function findById(int $id): ?Label;

    public function findByName(string $name): ?Label;

    public function create(array $data): Label;

    public function hasTask(Label $label): bool;

    public function update(Label $label, array $data): Label;

    public function delete(Label $label): void;
}
