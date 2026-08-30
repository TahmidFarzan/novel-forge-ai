<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AiBrainService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AiBrainController extends Controller
{
    protected AiBrainService $aiBrainService;

    public function __construct(AiBrainService $aiBrainService)
    {
        $this->aiBrainService = $aiBrainService;
    }

    public function index(Request $request): InertiaResponse
    {
        $aiBrain = $this->aiBrainService->new();
        Gate::authorize('viewAny', $aiBrain);

        $aiBrains = $this->aiBrainService->search($request);

        return Inertia::render('ai-brains/Index', [
            'aiBrains' => $aiBrains,
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $aiBrain = $this->aiBrainService->find($slug);

        Gate::authorize('view', $aiBrain);

        return Inertia::render('ai-brains/Details', [
            'aiBrain' => $aiBrain,
        ]);
    }
}
