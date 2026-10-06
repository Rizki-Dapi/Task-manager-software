<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserNotProjectMemberException extends Exception
{
    public function __construct(string $message = 'This user is not a member of this project.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'forbidden',
            'message' => $this->getMessage(),
        ], Response::HTTP_FORBIDDEN);
    }
}
