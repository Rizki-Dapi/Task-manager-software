<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/refresh', [AuthController::class, 'refreshToken'])->middleware('throttle:6,1');
    Route::middleware('jwt.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('jwt.auth')->group(function () {
    Route::prefix('/user')->group(function () {
        Route::get('/index-members', [UserController::class, 'indexMembers'])->middleware('role:super_admin|admin');
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/id/{userId}', [UserController::class, 'showById']);
            Route::get('/name/{name}', [UserController::class, 'showByName']);
            Route::get('/paginate', [UserController::class, 'paginate']);
            Route::get('/paginate/role', [UserController::class, 'paginateByRole']);
            Route::get('/countByRole/{role}', [UserController::class, 'countByRole']);
            Route::post('/', [UserController::class, 'store']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{userId}', [UserController::class, 'destroy']);
        });
    });

    Route::prefix('/label')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/', [LabelController::class, 'index']);
            Route::get('/{name}', [LabelController::class, 'showByName']);
            Route::post('/', [LabelController::class, 'store']);
            Route::put('/{label}', [LabelController::class, 'update']);
            Route::delete('/{labelId}', [LabelController::class, 'destroy']);
        });
    });

    Route::prefix('/project')->group(function () {
        Route::get('/show/{projectId}', [ProjectController::class, 'showProject']);
        Route::get('/', [ProjectController::class, 'paginateAll'])->middleware('role:super_admin');
        Route::get('/members', [ProjectController::class, 'paginateByMember'])->middleware('role:member');
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin', [ProjectController::class, 'paginateByCreator']);
            Route::post('/', [ProjectController::class, 'store']);
            Route::put('/{project}', [ProjectController::class, 'update'])->middleware('owner:project');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware('owner:project');
        });
    });

    Route::prefix('/project-member')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/project/{project}/member/{userId}', [ProjectMemberController::class, 'show']);
            Route::middleware('owner:project')->group(function () {
                Route::get('/index-members/{project}', [ProjectMemberController::class, 'indexMembers']);
                Route::post('/project/{project}', [ProjectMemberController::class, 'addMember']);
                Route::delete('/project/{project}/member/{userId}', [ProjectMemberController::class, 'removeMember']);
            });
        });
    });

    Route::prefix('/task')->group(function () {
        Route::get('/{taskId}', [TaskController::class, 'show']);
        Route::get('/index/{projectId}', [TaskController::class, 'indexAndFilter']);
        Route::get('/label/count-label', [TaskController::class, 'countByLabel'])->middleware('role:super_admin');
        Route::put('/assign/{task}', [TaskController::class, 'assign'])->middleware('role:member');
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [TaskController::class, 'store']);
            Route::middleware('owner:task')->group(function () {
                Route::put('/{task}', [TaskController::class, 'update']);
                Route::put('/unAssign/{task}', [TaskController::class, 'unAssign']);
                Route::delete('/{task}', [TaskController::class, 'destroy']);
            });
        });
    });
});
