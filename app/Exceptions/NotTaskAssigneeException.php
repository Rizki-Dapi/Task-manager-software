<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NotTaskAssigneeException extends Exception
{
    public function __construct(string $message = 'Your are not the assignee of this task.')
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
