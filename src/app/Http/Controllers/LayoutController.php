<?php

namespace App\Http\Controllers;

use App\Services\LayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LayoutController extends Controller
{
    protected LayoutService $layoutService;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }

    public function aiBrains(Request $request): JsonResponse
    {
        return response()->json(
            $this->layoutService->getAvailableAiBrains($request)
        );
    }
}