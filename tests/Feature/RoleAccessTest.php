<?php

use App\Models\Project;
use App\Models\Task;

beforeEach(function () {
    $this->super_admin = createUserWithRole('super_admin');
    $this->admin = createUserWithRole('admin');
    $this->member = createUserWithRole('member');
});

it('cannot let a member create a label', function () {
    actingAsApi($this->member)->postJson('/api/label')
        ->assertStatus(403)
        ->assertJsonPath('error', 'Forbidden');
});

it('cannot let a member create a project', function () {
    actingAsApi($this->member)->postJson('/api/project')->assertStatus(403);
});

it('cannot let a member create a task', function () {
    actingAsApi($this->member)->postJson('/api/task')->assertStatus(403);
});

it('cannot let a member add a project member', function () {
    $project = Project::factory()->create();

    actingAsApi($this->member)
        ->postJson("/api/project-member/project/{$project->id}", [
            'user_id' => $this->admin->id,
        ])
        ->assertStatus(403);
});

it('cannot let an admin create a user', function () {
    actingAsApi($this->admin)->postJson('/api/user')->assertStatus(403);
});

it('cannot let an admin list all projects', function () {
    actingAsApi($this->admin)->getJson('/api/project')->assertStatus(403);
});

it('cannot let an admin assign a task', function () {
    $task = Task::factory()->create();

    actingAsApi($this->admin)->putJson("/api/task/assign/{$task->id}")->assertStatus(403);
});
