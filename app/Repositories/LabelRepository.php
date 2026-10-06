<?php

namespace App\Repositories;

use App\Models\Label;
use App\Repositories\Interfaces\LabelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Override;

class LabelRepository implements LabelRepositoryInterface
{
    #[Override]
    public function get(): Collection
    {
        return Label::all();
    }

    public function findById(int $id): ?Label
    {
        return Label::find($id);
    }

    #[Override]
    public function findByName(string $name): ?Label
    {
        return Label::where('name', $name)->first();
    }

    #[Override]
    public function create(array $data): Label
    {
        return Label::create($data);
    }

    #[Override]
    public function hasTask(Label $label): bool
    {
        return $label->tasks()->exists();
    }

    #[Override]
    public function update(Label $label, array $data): Label
    {
        $label->update($data);

        return $label->refresh();
    }

    #[Override]
    public function delete(Label $label): void
    {
        $label->delete();
    }
}
