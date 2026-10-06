<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Services\ProjectMemberService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectMemberController extends Controller
{
    public function __construct(private readonly ProjectMemberService $projectMemberService) {}

    public function show(Project $project, int $userId): JsonResponse
    {
        $data = $this->projectMemberService->findByProjectAndUser($project, $userId);

        return response()->json(ApiFormatter::createJson('data retrieved succesffully', [
            'data' => new ProjectMemberResource($data),
        ]), Response::HTTP_OK);
    }

    public function indexMembers(Project $project): JsonResponse
    {
        $members = $this->projectMemberService->listMember($project);

        return response()->json(ApiFormatter::createJson('member retrieved succesffully', [
            'member' => ProjectMemberResource::collection($members),
        ]), Response::HTTP_OK);
    }

    public function addMember(AddMemberRequest $req, Project $project): JsonResponse
    {
        $userId = $req->validated('user_id');

        $member = $this->projectMemberService->addMember($project, $userId);

        return response()->json(ApiFormatter::createJson('member add to project successfully', [
            'member' => new ProjectMemberResource($member),
        ]), Response::HTTP_CREATED);
    }

    public function removeMember(Project $project, int $userId): JsonResponse
    {
        $this->projectMemberService->removeMember($project, $userId);

        return response()->json(ApiFormatter::createJson('member removed successfully'), Response::HTTP_OK);
    }
}
