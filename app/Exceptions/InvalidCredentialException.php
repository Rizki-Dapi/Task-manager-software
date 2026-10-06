<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InvalidCredentialException extends Exception
{
    public function __construct(string $message = 'Invalid Email or Password. ')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'unauthorized',
            'message' => $this->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
