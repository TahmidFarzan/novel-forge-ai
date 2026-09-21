<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Services\BackOffice\NovelGeneratorStepService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NovelGeneratorStepController extends Controller
{
    protected NovelGeneratorStepService $novelGeneratorStepService;

    public function __construct(NovelGeneratorStepService $novelGeneratorStepService)
    {
        $this->novelGeneratorStepService = $novelGeneratorStepService;
    }

    public function index(Request $request): InertiaResponse
    {
        $novelGeneratorStep = $this->novelGeneratorStepService->new();

        Gate::authorize('viewAny', $novelGeneratorStep);

        return Inertia::render('back-office/novel-generator-steps/Index', [
            'novelGeneratorSteps' => $this->novelGeneratorStepService->search($request),
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $novelGeneratorStep = $this->novelGeneratorStepService->find($slug);

        $novelGeneratorStep->setRelation(
            'dependencySteps',
            $novelGeneratorStep->dependencySteps()->get()
        );

        Gate::authorize('view', $novelGeneratorStep);

        return Inertia::render('back-office/novel-generator-steps/Details', [
            'novelGeneratorStep' => $novelGeneratorStep,
        ]);
    }
}
