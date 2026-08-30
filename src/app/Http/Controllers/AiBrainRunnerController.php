<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AiBrainRunnerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AiBrainRunnerController extends Controller
{
    protected AiBrainRunnerService $aiBrainRunnerService;

    public function __construct(AiBrainRunnerService $aiBrainRunnerService)
    {
        $this->aiBrainRunnerService = $aiBrainRunnerService;
    }

    public function index(Request $request): InertiaResponse
    {
        $aiBrainRunner = $this->aiBrainRunnerService->new();
        Gate::authorize('viewAny', $aiBrainRunner);

        $aiBrainRunners = $this->aiBrainRunnerService->search($request);

        return Inertia::render('ai-brain-runners/Index', [
            'aiBrainRunners' => $aiBrainRunners,
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $aiBrainRunner = $this->aiBrainRunnerService->find($slug);

        Gate::authorize('view', $aiBrainRunner);

        return Inertia::render('ai-brain-runners/Details', [
            'aiBrainRunner' => $aiBrainRunner,
        ]);
    }
}
