<?php

namespace App\Http\Controllers;


use App\Services\NovelGeneratorService;
use App\Http\Requests\NovelGeneratorStep1Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NovelGeneratorController extends Controller
{
    protected NovelGeneratorService $novelGeneratorService;

    public function __construct(NovelGeneratorService $novelGeneratorService)
    {
        $this->novelGeneratorService = $novelGeneratorService;
    }

    public function index(Request $request): InertiaResponse
    {
        $novelGenerator = $this->novelGeneratorService->new();

        Gate::authorize('viewAny', $novelGenerator);

        return Inertia::render('novel-generators/Index', [
            'novelGenerators' => $this->novelGeneratorService->search($request),
        ]);
    }

    public function save(NovelGeneratorStep1Request $request): RedirectResponse
    {
        $novelGenerator = $this->novelGeneratorService->new();
        Gate::authorize('create', $novelGenerator);

        $result = $this->novelGeneratorService->step1Save($request, $novelGenerator);

        return to_route('novel-generators.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }
}
