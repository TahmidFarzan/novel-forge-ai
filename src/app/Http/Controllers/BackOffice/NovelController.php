<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Helpers\NovelHelper;
use App\Http\Requests\NovelChapterContentRequest;
use App\Http\Requests\NovelChapterPlannerRequest;
use App\Http\Requests\NovelChapterSummaryRequest;
use App\Http\Requests\NovelCreaturesRequest;
use App\Http\Requests\NovelDialoguePlannerRequest;
use App\Http\Requests\NovelFactionsRequest;
use App\Http\Requests\NovelFoundationRequest;
use App\Http\Requests\NovelLocationsRequest;
use App\Http\Requests\NovelPagePlannerRequest;
use App\Http\Requests\NovelScenePlannerRequest;
use App\Http\Requests\NovelStoryStructureRequest;
use App\Http\Requests\NovelSystemsRequest;
use App\Http\Requests\NovelTimelineRequest;
use App\Http\Requests\NovelCharactersRequest;
use App\Http\Requests\NovelTwistsAndForeshadowingRequest;
use App\Http\Requests\NovelWorldBibleRequest;
use App\Services\BackOffice\NovelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NovelController extends Controller
{
    protected NovelService $novelService;

    public function __construct(NovelService $novelService)
    {
        $this->novelService = $novelService;
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
        ]);
    }


    public function createFoundation(NovelFoundationRequest $request): RedirectResponse
    {
        $novel = $this->novelService->new();
        Gate::authorize('create', $novel);

        $result = $this->novelService->generateFoundation($request, $novel);

        if ($result['novel']?->slug) {
            return to_route('back-office.novels.edit', ["slug" => $result['novel']?->slug])->with('flash_message', [
                'message' => $result['message'],
                'status'  => $result['status'],
            ]);
        }
        return to_route('back-office.novels.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateFoundation(NovelFoundationRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateFoundation($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateCharacters(NovelCharactersRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateCharacters($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateWorldBible(NovelWorldBibleRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateWorldBible($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateLocations(NovelLocationsRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateLocations($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateFactions(NovelFactionsRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateFactions($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateCreature(NovelCreaturesRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateCreature($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateSystem(NovelSystemsRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateSystem($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateTimeline(NovelTimelineRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateTimeline($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStoryStructure(NovelStoryStructureRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStoryStructure($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateTwistsAndForeshadowing(NovelTwistsAndForeshadowingRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateTwistsAndForeshadowing($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateScenePlanner(NovelScenePlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateScenePlanner($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateDialoguePlanner(NovelDialoguePlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateDialoguePlanner($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateChapterPlanner(NovelChapterPlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateChapterPlanner($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generatePagePlanner(NovelPagePlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generatePagePlanner($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateChapterSummaries(NovelChapterSummaryRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateChapterSummaries($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateChapterContent(NovelChapterContentRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateChapterContent($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function reviewNovel(string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->reviewNovel($novel);

        if ($result['status'] === 'success') {
            return to_route('back-office.novels.index')->with('flash_message', [
                'message' => $result['message'],
                'status'  => $result['status'],
            ]);
        }

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function delete(string $slug): RedirectResponse
    {
        $user = $this->novelService->find($slug);

        Gate::authorize('delete', $user);

        $result = $this->novelService->delete($user);

        return to_route('back-office.novels.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }
}
