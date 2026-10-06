<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService) {}

    public function showProject(int $projectId): JsonResponse
    {
        $project = $this->projectService->findById($projectId, auth('api')->user());

        return response()->json(ApiFormatter::createJson('project retrieved succesffully', [
            'project' => new ProjectResource($project),
        ]), Response::HTTP_OK);
    }

    public function paginateAll(Request $req): JsonResponse
    {
        $projects = $this->projectService->paginateAll(
            filters: $req->only(['search', 'status']),
            perPage: (int) $req->query('per_page', 10),
        );

        return response()->json(ApiFormatter::createJson('projects retirieved successfully', [
            'projects' => ProjectResource::collection($projects),
        ]), Response::HTTP_OK);
    }

    public function paginateByCreator(Request $req): JsonResponse
    {
        $projects = $this->projectService->paginateByCreator(
            auth('api')->id(),
            filters: $req->only(['search', 'status']),
            perPage: (int) $req->query('per_page', 10),
        );

        return response()->json(ApiFormatter::createJson('projects retrieved succcesfully', [
            'projects' => ProjectResource::collection($projects),
        ]), Response::HTTP_OK);
    }

    public function paginateByMember(Request $req): JsonResponse
    {
        $projects = $this->projectService->paginateByMember(
            auth('api')->id(),
            filters: $req->only(['search', 'status']),
            perPage: (int) $req->query('per_page', 10),
        );

        return response()->json(ApiFormatter::createJson('Projects retrieved successfully', [
            'projects' => ProjectResource::collection($projects),
        ]), Response::HTTP_OK);
    }

    public function store(StoreProjectRequest $req): JsonResponse
    {
        $project = $this->projectService->create($req->validated(), auth('api')->user());

        return response()->json(ApiFormatter::createJson('Project created succesfully', [
            'project' => new ProjectResource($project),
        ]), Response::HTTP_CREATED);
    }

    public function update(UpdateProjectRequest $req, Project $project): JsonResponse
    {
        $data = $this->projectService->update($project, $req->validated());

        return response()->json(ApiFormatter::createJson('project Updated successfully', [
            'project' => new ProjectResource($data),
        ]), Response::HTTP_OK);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->projectService->delete($project);

        return response()->json(ApiFormatter::createJson('Project Deleted successfully'), Response::HTTP_OK);
    }
}
