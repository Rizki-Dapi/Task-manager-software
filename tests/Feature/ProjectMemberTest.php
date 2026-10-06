<?php

use App\Models\Project;
use App\Models\ProjectMember;

beforeEach(function () {
    $this->super_admin = createUserWithRole('super_admin');
    $this->admin = createUserWithRole('admin');
    $this->member = createUserWithRole('member');
});

it('cannot add a user who is already a member', function () {
    $project = Project::factory()->create();
    $user = memberOf($project);

    actingAsApi($this->admin)
        ->postJson("/api/project-member/project/{$project->id}", ['user_id' => $user->id])
        ->assertStatus(409);

    expect(ProjectMember::where('project_id', $project->id)->where('user_id', $user->id)->count())->toBe(1);
});

it('only removes the given member from the project', function () {
    $project = Project::factory()->create();
    $leavingUser = memberOf($project);
    $stayingUser = memberOf($project);

    actingAsApi($this->admin)
        ->deleteJson("/api/project-member/project/{$project->id}/member/{$leavingUser->id}")
        ->assertStatus(200);

    expect(ProjectMember::where('project_id', $project->id)->pluck('user_id')->all())
        ->toBe([$stayingUser->id]);
});
