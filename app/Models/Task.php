<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'title',
    'description',
    'status',
    'assignee_id',
    'label_id',
])]
class Task extends Model
{
    use HasFactory;

    protected $with = ['assigned', 'labels', 'projects'];

    protected $attributes = ['status' => 'in-progress'];

    public function assigned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function labels(): BelongsTo
    {
        return $this->belongsTo(Label::class, 'label_id');
    }

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
