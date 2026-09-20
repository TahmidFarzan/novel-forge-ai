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


    public function createStep1(NovelFoundationRequest $request): RedirectResponse
    {
        $novel = $this->novelService->new();
        Gate::authorize('create', $novel);

        $result = $this->novelService->generateStep1($request, $novel);

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

    public function generateStep1(NovelFoundationRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep1($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep2(NovelCharactersRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep2($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep3(NovelWorldBibleRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep3($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep4(NovelLocationsRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep4($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep5(NovelFactionsRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep5($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep6(NovelCreaturesRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep6($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep7(NovelSystemsRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep7($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep8(NovelTimelineRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep8($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep9(NovelStoryStructureRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep9($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep10(NovelTwistsAndForeshadowingRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep10($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep11(NovelScenePlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep11($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep12(NovelDialoguePlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep12($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep13(NovelChapterPlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep13($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep14(NovelPagePlannerRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep14($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep15(NovelChapterSummaryRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep15($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep16(NovelChapterContentRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep16($request, $novel);

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
