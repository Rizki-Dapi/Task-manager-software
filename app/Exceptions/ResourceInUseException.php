<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResourceInUseException extends Exception
{
    public function __construct(string $message = 'This resource cannot be deleted because it is still in use.')
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
