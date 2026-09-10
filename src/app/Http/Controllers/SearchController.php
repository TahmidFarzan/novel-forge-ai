<?php
namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
        $this->middleware(['auth', 'verified'])->only(['user']);
    }

    public function perPages(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->perPages($request)
        );
    }

    public function genders(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->genders($request)
        );
    }

    public function religions(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->religions($request)
        );
    }

    public function maritalStatuses(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->maritalStatuses($request)
        );
    }

    public function activityLogEvents(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->activityLogEvents($request)
        );
    }

    public function activityLogSubjectTypes(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->activityLogSubjectTypes($request)
        );
    }

    public function novelContinuities(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->novelContinuities($request)
        );
    }

    public function novelGeneratorStatuses(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->novelGeneratorStatuses($request)
        );
    }

    public function novelGeneratorStepStatuses(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->novelGeneratorStepStatuses($request)
        );
    }

    public function users(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->users($request)
        );
    }

    public function genres(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->genres($request)
        );
    }

    public function novelTypes(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->novelTypes($request)
        );
    }

    public function audiences(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->audiences($request)
        );
    }

    public function languages(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->languages($request)
        );
    }

    public function aiBrains(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->aiBrains($request)
        );
    }

    public function kdpLayouts(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->kdpLayouts($request)
        );
    }

    public function aiPrompts(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->aiPrompts($request)
        );
    }

    public function documentStyles(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->documentStyles($request)
        );
    }

    public function user(string | int $slugOrId): JsonResponse
    {
        return response()->json(
            $this->searchService->user($slugOrId)
        );
    }

    public function userPermissions(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->userPermissions($request)
        );
    }

    public function aiBrain(string | int $slugOrId): JsonResponse
    {
        return response()->json(
            $this->searchService->aiBrain($slugOrId)
        );
    }

    public function userPermission(string | int $slugOrId): JsonResponse
    {
        return response()->json(
            $this->searchService->userPermission($slugOrId)
        );
    }

    public function userPermissionsByGroup(Request $request): JsonResponse
    {
        return response()->json(
            $this->searchService->userPermissionsByGroup($request)
        );
    }
}
