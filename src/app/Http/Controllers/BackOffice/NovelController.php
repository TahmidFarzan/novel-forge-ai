<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Helpers\NovelHelper;
use App\Http\Requests\StoryBookStep1;
use App\Services\BackOffice\NovelGeneratorStepService;
use App\Services\BackOffice\NovelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NovelController extends Controller
{
    protected NovelService $novelService;
    protected NovelGeneratorStepService $novelGeneratorStepService;

    public function __construct(NovelService $novelService, NovelGeneratorStepService $novelGeneratorStepService)
    {
        $this->novelService = $novelService;
        $this->novelGeneratorStepService = $novelGeneratorStepService;
    }

    public function index(Request $request): InertiaResponse
    {
        $novel = $this->novelService->new();

        Gate::authorize('viewAny', $novel);

        return Inertia::render('back-office/novels/Index', [
            'novels' => $this->novelService->search($request),
        ]);
    }

    public function create(): InertiaResponse
    {
        $novel = $this->novelService->new();
        Gate::authorize('view', $novel);

        return Inertia::render('back-office/novels/Create', [
            'novel' => $novel,
            'generationSteps' => $this->generationSteps(),
        ]);
    }

    public function edit(string $slug): RedirectResponse|InertiaResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        if ($novel->status === NovelHelper::STATUS_COMPLETE) {
            return to_route('back-office.novels.index')->with('flash_message', [
                'message' => 'This novel is already completed and locked from further generation.',
                'status'  => 'info',
            ]);
        }

        return Inertia::render('back-office/novels/Create', [
            'novel' => $novel,
            'generationSteps' => $this->generationSteps(),
        ]);
    }

    public function createGenerate(StoryBookStep1 $request): RedirectResponse
    {
        $novel = $this->novelService->new();
        Gate::authorize('create', $novel);

        $result = $this->novelService->createFromFoundation($request);

        if ($result['status'] !== 'success' || ! $result['novel']?->slug) {
            return to_route('back-office.novels.create')->with('flash_message', [
                'message' => $result['message'],
                'status'  => $result['status'],
            ]);
        }

        return $this->editRedirect($result['novel']->slug, true, $result['message'], $result['status']);
    }

    public function generate(string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generate($novel);

        if ($novel->status === NovelHelper::STATUS_COMPLETE) {
            return to_route('back-office.novels.index')->with('flash_message', [
                'message' => $result['message'],
                'status'  => $result['status'],
            ]);
        }

        if ($result['status'] === 'error') {
            return $this->editRedirect($slug, false, $result['message'], $result['status']);
        }

        return $this->editRedirect($slug, true, $result['message'], $result['status']);
    }

    public function stop(string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->stop($novel);

        return $this->editRedirect($slug, false, $result['message'], $result['status'] === 'success' ? 'info' : $result['status']);
    }

    public function delete(string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);

        Gate::authorize('delete', $novel);

        $result = $this->novelService->delete($novel);

        return to_route('back-office.novels.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    private function editRedirect(string $slug, bool $auto, string $message, string $status): RedirectResponse
    {
        $parameters = $auto ? ['slug' => $slug, 'auto' => 1] : ['slug' => $slug];

        return to_route('back-office.novels.edit', $parameters)->with('flash_message', [
            'message' => $message,
            'status'  => $status,
        ]);
    }

    private function generationSteps(): array
    {
        return $this->novelGeneratorStepService->orderedSteps()
            ->values()
            ->map(fn ($step) => [
                'id' => $step->id,
                'name' => $step->name,
            ])
            ->values()
            ->all();
    }
}