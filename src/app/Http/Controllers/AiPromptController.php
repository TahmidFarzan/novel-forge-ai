<?php
namespace App\Http\Controllers;

use App\Http\Requests\AiPromptRequest;
use App\Services\AiPromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AiPromptController extends Controller
{
    protected AiPromptService $aiPromptService;

    public function __construct(AiPromptService $aiPromptService)
    {
        $this->aiPromptService = $aiPromptService;
    }

    public function index(Request $request): InertiaResponse
    {
        $aiPrompt = $this->aiPromptService->new();

        Gate::authorize('viewAny', $aiPrompt);

        return Inertia::render('ai-prompts/Index', [
            'aiPrompts' => $this->aiPromptService->search($request),
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $aiPrompt = $this->aiPromptService->find($slug);

        Gate::authorize('view', $aiPrompt);

        return Inertia::render('ai-prompts/Details', [
            'aiPrompt' => $aiPrompt,
        ]);
    }

    public function edit(string $slug): InertiaResponse
    {
        $aiPrompt = $this->aiPromptService->find($slug);

        Gate::authorize('update', $aiPrompt);

        return Inertia::render('ai-prompts/Create', [
            'aiPrompt' => $aiPrompt,
        ]);
    }

    public function save(AiPromptRequest $request): RedirectResponse
    {
        $aiPrompt = $this->aiPromptService->new();

        Gate::authorize('create', $aiPrompt);

        $result = $this->aiPromptService->save($request, $aiPrompt);

        return to_route('ai-prompts.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function update(AiPromptRequest $request, string $slug): RedirectResponse
    {
        $aiPrompt = $this->aiPromptService->find($slug);

        Gate::authorize('update', $aiPrompt);

        $result = $this->aiPromptService->save($request, $aiPrompt);

        return to_route('ai-prompts.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }
}
