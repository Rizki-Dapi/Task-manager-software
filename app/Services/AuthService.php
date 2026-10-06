<?php

namespace App\Services;

use App\Exceptions\InvalidCredentialException;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly LogRepositoryInterface $logRepository,
    ) {}

    public function login(array $credentials): array
    {
        if (! $token = JWTAuth::attempt($credentials)) {
            throw new InvalidCredentialException;
        }

        $user = JWTAuth::user();

        if (Hash::needsRehash($user->password)) {
            $this->userRepository->update($user, ['password' => $credentials['password']]);
        }

        $user = auth('api')->user();

        $this->logRepository->record([
            'user_id' => $user->id,
            'action' => 'user.login',
        ]);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(): void
    {
        $userId = auth('api')->id();

        JWTAuth::invalidate(JWTAuth::getToken());

        $this->logRepository->record([
            'user_id' => $userId,
            'action' => 'user.logout',
        ]);
    }

    public function me(): ?User
    {
        return auth('api')->user();
    }

    public function refreshToken(): array
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            throw new InvalidCredentialException('Your session has expired, please log in again.');
        }

        $user = JWTAuth::setToken($newToken)->authenticate()->load('roles');

        $this->logRepository->record([
            'user_id' => $user->id,
            'action' => 'user.token_refreshed',
        ]);

        return ['user' => $user, 'token' => $newToken];
    }
}
