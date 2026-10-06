<?php

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\Label;
use App\Repositories\Interfaces\LabelRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LabelService
{
    public function __construct(
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly LogRepositoryInterface $logRepository,
    ) {}

    public function get(): Collection
    {
        return $this->labelRepository->get();
    }

    public function findById(int $labelId): Label
    {
        $label = $this->labelRepository->findById($labelId);

        if (! $label) {
            throw new ModelNotFoundException('label not found');
        }

        return $label;
    }

    public function findByName(string $name): Label
    {
        $label = $this->labelRepository->findByName($name);

        if (! $label) {
            throw new ModelNotFoundException('label not found');
        }

        return $label;
    }

    public function create(array $data): Label
    {
        $label = $this->labelRepository->create($data);

        $this->logRepository->record([
            'action' => 'admin.label_create',
            'context' => ['label_id' => $label->id],
        ]);

        return $label;
    }

    public function updated(Label $label, array $data): Label
    {
        $updated = $this->labelRepository->update($label, $data);

        $this->logRepository->record([
            'action' => 'admin.label_updated',
            'context' => ['label_id' => $label->id, 'field' => array_keys($data)],
        ]);

        return $updated;
    }

    public function delete(Label $label): void
    {
        if ($this->labelRepository->hasTask($label)) {
            throw new ResourceInUseException(
                'Cannot delete this label because it still has task. Delete the task first. '
            );
        }

        $this->labelRepository->delete($label);

        $this->logRepository->record([
            'action' => 'admin.delete_label',
            'context' => ['label_id' => $label->id],
        ]);
    }
}
