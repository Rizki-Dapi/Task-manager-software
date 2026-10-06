<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'project' => $this->whenLoaded('projects', fn () => [
                'id' => $this->projects->id,
                'name' => $this->projects->name,
            ]),
            'description' => $this->description,
            'status' => $this->status,
            'label' => $this->whenLoaded('labels', fn () => [
                'id' => $this->labels->id,
                'name' => $this->labels->name,
            ]),
            'assignee' => $this->whenLoaded('assigned', fn () => [
                'id' => $this->assigned->id,
                'name' => $this->assigned->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
