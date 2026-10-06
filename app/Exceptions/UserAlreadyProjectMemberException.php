<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserAlreadyProjectMemberException extends Exception
{
    public function __construct(string $message = 'This user is already a member for this project. ')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'conflict',
            'message' => $this->getMessage(),
        ], Response::HTTP_CONFLICT);
    }
}
