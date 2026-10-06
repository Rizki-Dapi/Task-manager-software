<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'project_id'])]
class ProjectMember extends Model
{
    use HasFactory;

    protected $with = [
        'user',
        'projects'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
