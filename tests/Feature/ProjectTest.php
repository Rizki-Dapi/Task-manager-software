<?php

use App\Models\Project;
use App\Models\Task;

beforeEach(function () {
    $this->super_admin = createUserWithRole('super_admin');
    $this->admin = createUserWithRole('admin');
    $this->member = createUserWithRole('member');
});

it('can get project by id as project owner', function () {
    $project = Project::factory()->create([
        'created_by' => $this->admin->id,
    ]);

    actingAsApi($this->admin)
        ->getJson("/api/project/show/{$project->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.project.id', $project->id);
});

it('cannot delete a project that still has an assigned task', function () {
    $admin = createUser('admin');
    $project = Project::factory()->create(['created_by' => $admin->id]);

    Task::factory()->create([
        'project_id' => $project->id,
        'assignee_id' => memberOf($project)->id,
        'status' => 'completed',
    ]);

    actingAsApi($admin)->deleteJson("/api/project/{$project->id}")->assertStatus(409);

    expect(Project::find($project->id))->not->toBeNull();
});

it('deletes the tasks together with the project when none is assigned', function () {
    $admin = createUser('admin');
    $project = Project::factory()->create(['created_by' => $admin->id]);
    $task = Task::factory()->create(['project_id' => $project->id]);

    actingAsApi($admin)->deleteJson("/api/project/{$project->id}")->assertStatus(200);

    expect(Project::find($project->id))->toBeNull()
        ->and(Task::find($task->id))->toBeNull();
});

it('can let a super admin see any project', function () {
    $project = Project::factory()->create();

    actingAsApi(createUser('super_admin'))->getJson("/api/project/show/{$project->id}")
        ->assertStatus(200);
});

it('cannot show a project created by another admin', function () {
    $admin = createUser('admin');

    $own = Project::factory()->create(['created_by' => $admin->id]);
    $others = Project::factory()->create();

    actingAsApi($admin)->getJson("/api/project/show/{$own->id}")->assertStatus(200);
    actingAsApi($admin)->getJson("/api/project/show/{$others->id}")->assertStatus(403);
});

it('only returns projects the member belongs to', function () {
    $joined = Project::factory()->create();
    $member = memberOf($joined);

    Project::factory()->create();

    $projects = actingAsApi($member)
        ->getJson('/api/project/members')
        ->assertStatus(200)
        ->json('data.projects');

    expect($projects)->toHaveCount(1)
        ->and($projects[0]['id'])->toBe($joined->id);
});

it('only returns projects created by the authenticated admin', function () {
    $admin = createUser('admin');

    $own = Project::factory()->create(['created_by' => $admin->id]);
    Project::factory()->create();

    $projects = actingAsApi($admin)
        ->getJson('/api/project/admin')
        ->assertStatus(200)
        ->json('data.projects');

    expect($projects)->toHaveCount(1)
        ->and($projects[0]['id'])->toBe($own->id);
});
