<?php

use App\Enums\RoleUserEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->app->instance(LogRepositoryInterface::class, Mockery::spy(LogRepositoryInterface::class));
    })
    // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function actingAsApi(User $user): TestCase
{
    $token = JWTAuth::fromUser($user);

    return test()->withHeader('Authorization', "Bearer {$token}");
}

function createUserWithRole(string $role = RoleUserEnum::MEMBER->value, array $attributes = []): User
{
    return User::factory()->create([
        'role_id' => Role::firstOrCreate(['name' => $role])->id,
        ...$attributes,
    ]);
}

function createUser(string $role = RoleUserEnum::MEMBER->value, array $attributes = []): User
{
    return createUserWithRole($role, $attributes);
}

function memberOf(Project $project): User
{
    $user = User::factory()->member()->create();

    ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    return $user;
}
