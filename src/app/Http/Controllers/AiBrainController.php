<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiBrainRequest;
use App\Services\AiBrainService;
use Illuminate\Http\RedirectResponse;
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

        return Inertia::render('ai-brains/Index', [
            'aiBrains' => $this->aiBrainService->search($request),
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

    public function create(): InertiaResponse
    {
        $aiBrain = $this->aiBrainService->new();

        Gate::authorize('create', $aiBrain);

        return Inertia::render('ai-brains/Create', [
            'aiBrain' => $aiBrain,
        ]);
    }

    public function edit(string $slug): InertiaResponse
    {
        $aiBrain = $this->aiBrainService->find($slug);

        Gate::authorize('update', $aiBrain);

        return Inertia::render('ai-brains/Create', [
            'aiBrain' => $aiBrain,
        ]);
    }

    public function save(AiBrainRequest $request): RedirectResponse
    {
        $aiBrain = $this->aiBrainService->new();

        Gate::authorize('create', $aiBrain);

        $result = $this->aiBrainService->save($request, $aiBrain);

        return redirect()
            ->route('ai-brains.index')
            ->with('flash_message', [
                'message' => $result['message'],
                'status' => $result['status'],
            ]);
    }

    public function update(AiBrainRequest $request, string $slug): RedirectResponse
    {
        $aiBrain = $this->aiBrainService->find($slug);

        Gate::authorize('update', $aiBrain);

        $result = $this->aiBrainService->save($request, $aiBrain);

        return redirect()
            ->route('ai-brains.index')
            ->with('flash_message', [
                'message' => $result['message'],
                'status' => $result['status'],
            ]);
    }

    public function delete(string $slug): RedirectResponse
    {
        $aiBrain = $this->aiBrainService->find($slug);

        Gate::authorize('delete', $aiBrain);

        $result = $this->aiBrainService->delete($aiBrain);

        return redirect()
            ->route('ai-brains.index')
            ->with('flash_message', [
                'message' => $result['message'],
                'status' => $result['status'],
            ]);
    }
}
