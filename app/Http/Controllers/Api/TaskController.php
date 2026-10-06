<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function show(int $taskId): JsonResponse
    {
        $task = $this->taskService->findById($taskId, auth('api')->user());

        return response()->json(ApiFormatter::createJson('task retrieved successfully', [
            'task' => new TaskResource($task),
        ]), Response::HTTP_OK);
    }

    public function indexAndFilter(Request $req, int $projectId): JsonResponse
    {
        $tasks = $this->taskService->getAndFilter(
            $projectId,
            auth('api')->user(),
            filters: $req->only(['status', 'search']),
        );

        return response()->json(ApiFormatter::createJson('Tasks retrieved successfully', [
            'tasks' => TaskResource::collection($tasks),
        ]), Response::HTTP_OK);
    }

    public function countByLabel(): JsonResponse
    {
        $taskLabelCount = $this->taskService->countByLabel();

        return response()->json(ApiFormatter::createJson('count label retrieved', [
            'label' => $taskLabelCount,
        ]), Response::HTTP_OK);
    }

    public function store(StoreTaskRequest $req): JsonResponse
    {
        $task = $this->taskService->create($req->validated());

        return response()->json(ApiFormatter::createJson('task created successfully', [
            'task' => new TaskResource($task),
        ]), Response::HTTP_CREATED);
    }

    public function update(UpdateTaskRequest $req, Task $task): JsonResponse
    {
        $data = $this->taskService->update($task, $req->validated());

        return response()->json(ApiFormatter::createJson('task upadated successfully', [
            'task' => new TaskResource($data),
        ]), Response::HTTP_OK);
    }

    public function assign(Task $task): JsonResponse
    {
        $actingUser = auth('api')->user();

        $data = $this->taskService->assignTask($task, $actingUser);

        return response()->json(ApiFormatter::createJson('Assign task sucessfully', [
            'task' => new TaskResource($data),
        ]), Response::HTTP_OK);
    }

    public function unAssign(Task $task): JsonResponse
    {
        $data = $this->taskService->unAssign($task);

        return response()->json(ApiFormatter::createJson('UnAssign task successfully', [
            'task' => new TaskResource($data),
        ]), Response::HTTP_OK);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);

        return response()->json(ApiFormatter::createJson('Task Deleted sucessfully'), Response::HTTP_OK);
    }
}
