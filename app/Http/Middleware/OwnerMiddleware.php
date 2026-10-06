<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = auth('api')->user();

        if ($type === 'project') {
            $project = $request->route('project');

            if ($project->created_by !== $user->id) {
                abort(403, 'You are not the owner of this project.');
            }
        }

        if ($type === 'task') {
            $task = $request->route('task');

            if ($task->projects->created_by !== $user->id) {
                abort(403, 'You are not the owner of this task.');
            }
        }

        return $next($request);
    }
}
