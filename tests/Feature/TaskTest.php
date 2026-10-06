<?php

use App\Models\Label;
use App\Models\Project;
use App\Models\Task;

it('moves a completed project back to in-progress when a new task is added', function () {
    $admin = createUser('admin');
    $project = Project::factory()->create(['created_by' => $admin->id, 'status' => 'completed']);

    Task::factory()->create([
        'project_id' => $project->id,
        'assignee_id' => memberOf($project)->id,
        'status' => 'completed',
    ]);

    actingAsApi($admin)->postJson('/api/task', [
        'project_id' => $project->id,
        'title' => 'Task baru',
        'label_id' => Label::factory()->create()->id,
    ])->assertStatus(201);

    expect($project->refresh()->status)->toBe('in-progress');
});

it('stays in-progress when the last remaining task is deleted', function () {
    $admin = createUser('admin');
    $project = Project::factory()->create(['created_by' => $admin->id]);
    $only = Task::factory()->create(['project_id' => $project->id]);

    actingAsApi($admin)->deleteJson("/api/task/{$only->id}")->assertStatus(200);

    expect($project->refresh()->status)->toBe('in-progress');
});

it('can let a super admin see any task', function () {
    $task = Task::factory()->create();

    actingAsApi(createUser('super_admin'))->getJson("/api/task/{$task->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.task.id', $task->id);
});

it('cannot show a task from a project created by another admin', function () {
    $admin = createUser('admin');

    $own = Task::factory()->create([
        'project_id' => Project::factory()->create(['created_by' => $admin->id])->id,
    ]);
    $others = Task::factory()->create();

    actingAsApi($admin)->getJson("/api/task/{$own->id}")->assertStatus(200);
    actingAsApi($admin)->getJson("/api/task/{$others->id}")->assertStatus(422);
});
