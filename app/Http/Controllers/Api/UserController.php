<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function showById(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        return response()->json(ApiFormatter::createJson('User Retrieved succesully', [
            'user' => new UserResource($user),
        ]), Response::HTTP_OK);
    }

    public function showByName(string $name): JsonResponse
    {
        $user = $this->userService->findByName($name);

        return response()->json(ApiFormatter::createJson('User retrieved successfully', [
            'user' => new UserResource($user),
        ]), Response::HTTP_OK);
    }

    public function paginate(Request $req): JsonResponse
    {
        $users = $this->userService->paginate(
            search: $req->input('name'),
            perPage: (int) $req->query('per_page', 10),
        );

        return response()->json(ApiFormatter::createJson('User retrieved successfully', [
            'user' => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]), Response::HTTP_OK);
    }

    public function paginateByRole(Request $req): JsonResponse
    {
        $users = $this->userService->paginateByRole(
            role: $req->input('role'),
            search: $req->input('search'),
            perPage: (int) $req->query('per_page', 10),
        );

        return response()->json(ApiFormatter::createJson('Users retrieved succesfully', [
            'users' => UserResource::collection($users),
            'pagination' => [
                'current' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]), Response::HTTP_OK);
    }

    public function countByRole(string $role): JsonResponse
    {
        $total = $this->userService->countByRole($role);

        return response()->json(ApiFormatter::createJson('Count data successfully', [
            'data' => [
                'total' => $total,
            ],
        ]), Response::HTTP_OK);
    }

    public function indexMembers(): JsonResponse
    {
        $users = $this->userService->getMembers();

        return response()->json(ApiFormatter::createJson('User retrieved succesffuly', [
            'user' => UserResource::collection($users),
        ]), Response::HTTP_OK);
    }

    public function store(StoreUserRequest $req): JsonResponse
    {
        $user = $this->userService->create($req->validated());

        return response()->json(ApiFormatter::createJson('User Created Successfuly', [
            'user' => new UserResource($user),
        ]), Response::HTTP_CREATED);
    }

    public function update(UpdateUserRequest $req, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $req->validated());

        return response()->json(ApiFormatter::createJson('User updated successfully', [
            'user' => new UserResource($user),
        ]), Response::HTTP_OK);
    }

    public function destroy(int $userId): JsonResponse
    {
        $user = $this->userService->findById($userId);

        $this->userService->delete($user);

        return response()->json(ApiFormatter::createJson('User Deleted successfully'), Response::HTTP_OK);
    }
}
