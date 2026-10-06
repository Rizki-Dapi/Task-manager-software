<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\Label;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AssignedTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'member')->firstOrFail();

        $user = User::updateOrCreate([
            'email' => 'member@example.com',
        ], [
            'name' => 'Member User',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);

        $project = Project::firstOrCreate(
            [
                'name' => 'Sample Project',
            ],
            [
                'description' => 'This is a sample project.',
                'created_by' => $user->id,
                'status' => StatusEnum::IN_PROGRESS,
            ]
        );

        $label = Label::firstOrCreate(
            [
                'name' => 'Sample Label',
            ],
        );

        Task::updateOrCreate(
            [
                'project_id' => $project->id,
            ],
            [
                'title' => 'Sample Task',
                'description' => 'This is a sample task.',
                'status' => StatusEnum::IN_PROGRESS,
                'assignee_id' => $user->id,
                'label_id' => $label->id,
            ]
        );
    }
}
