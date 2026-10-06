<?php

use App\Models\Label;
use App\Models\Task;

beforeEach(function () {
    $this->super_admin = createUserWithRole('super_admin');
    $this->admin = createUserWithRole('admin');
    $this->member = createUserWithRole('member');
});

it('Cannot delete a label that still has used by task', function () {
    $label = Label::factory()->create();
    Task::factory()->create(['label_id' => $label->id]);

    actingAsApi($this->admin)->deleteJson("/api/label/{$label->id}")
        ->assertStatus(409);

    expect(Label::find($label->id))->not()->toBeNull();
});
