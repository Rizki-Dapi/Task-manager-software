<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabelRequest;
use App\Http\Requests\UpdateLabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Services\LabelService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LabelController extends Controller
{
    public function __construct(private readonly LabelService $labelService) {}

    public function index(): JsonResponse
    {
        $labels = $this->labelService->get();

        return response()->json(ApiFormatter::createJson('label retrieved successfully', [
            'labels' => LabelResource::collection($labels),
        ]), Response::HTTP_OK);
    }

    public function showByName(string $name): JsonResponse
    {
        $label = $this->labelService->findByName($name);

        return response()->json(ApiFormatter::createJson('label retrieved succesfully', [
            'label' => new LabelResource($label),
        ]), Response::HTTP_OK);
    }

    public function store(StoreLabelRequest $req): JsonResponse
    {
        $label = $this->labelService->create($req->validated());

        return response()->json(ApiFormatter::createJson('label created successfully', [
            'label' => new LabelResource($label),
        ]), Response::HTTP_CREATED);
    }

    public function update(UpdateLabelRequest $req, Label $label): JsonResponse
    {
        $labels = $this->labelService->updated($label, $req->validated());

        return response()->json(ApiFormatter::createJson('label upadated succesffully', [
            'label' => new LabelResource($labels),
        ]), Response::HTTP_OK);
    }

    public function destroy(int $labelId): JsonResponse
    {
        $label = $this->labelService->findById($labelId);

        $this->labelService->delete($label);

        return response()->json(ApiFormatter::createJson('label deleted successfully'), Response::HTTP_OK);
    }
}
