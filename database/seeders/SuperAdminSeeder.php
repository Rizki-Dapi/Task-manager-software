<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'super_admin')->firstOrFail();

        User::firstOrCreate(
            [
                'name' => 'super_admin',
                'email' => 'superAdmin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
            ]
        );
    }
}
