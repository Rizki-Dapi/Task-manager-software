<?php

use App\Enums\StatusEnum;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;

use function PHPUnit\Framework\assertJson;

beforeEach(function () {
    $this->super_admin = createUserWithRole('super_admin');
    $this->admin = createUserWithRole('admin');
    $this->member = createUserWithRole('member');
});

it('cannot create a user with a name already use by an active user', function () {
    createUser(attributes: ['name' => 'john']);

    actingAsApi($this->super_admin)->postJson('/api/user/', [
        'name' => 'john',
        'email' => 'new@example.com',
        'password' => 'secret1',
        'role_id' => Role::factory()->member()->create()->id,
    ])->assertStatus(422)
        ->assertJsonStructure(['message' => ['name']]);
});

it('cannot delete a user who is still the asignee of task', function () {
    $member = createUser('member');
    Task::factory()->create([
        'assignee_id' => $member->id,
        'status' => StatusEnum::COMPLETED,
    ]);

    actingAsApi($this->super_admin)->deleteJson("/api/user/{$member->id}")
        ->assertStatus(409);

    expect(User::find($member->id))->not()->toBeNull();
});
