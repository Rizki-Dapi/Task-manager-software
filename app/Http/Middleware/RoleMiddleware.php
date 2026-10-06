<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('api')->user();

        $userRole = Role::find($user?->role_id)?->name;

        if (! $user || ! in_array($userRole, $roles, true)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have access to this resource',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
