<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $req): JsonResponse
    {
        $result = $this->authService->login($req->validated());

        return response()->json(ApiFormatter::createJson('login successful', [
            'user' => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
        ]), Response::HTTP_OK);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json(ApiFormatter::createJson('logout succesful'), Response::HTTP_OK);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return response()->json(ApiFormatter::createJson('User retrieved successfully', [
            'user' => new UserResource($user),
        ]), Response::HTTP_OK);
    }

    public function refreshToken(): JsonResponse
    {
        $result = $this->authService->refreshToken();

        return response()->json(ApiFormatter::createJson('Token Refreshed successfully', [
            'user' => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type' => 'Bearer'
        ]), Response::HTTP_OK);
    }
}
