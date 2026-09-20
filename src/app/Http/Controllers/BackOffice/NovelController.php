<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Helpers\NovelHelper;
use App\Http\Requests\StoryBookStep1;
use App\Http\Requests\StoryBookStep10;
use App\Http\Requests\StoryBookStep11;
use App\Http\Requests\StoryBookStep12;
use App\Http\Requests\StoryBookStep13;
use App\Http\Requests\StoryBookStep14;
use App\Http\Requests\StoryBookStep15;
use App\Http\Requests\StoryBookStep16;
use App\Http\Requests\StoryBookStep2;
use App\Http\Requests\StoryBookStep3;
use App\Http\Requests\StoryBookStep4;
use App\Http\Requests\StoryBookStep5;
use App\Http\Requests\StoryBookStep6;
use App\Http\Requests\StoryBookStep7;
use App\Http\Requests\StoryBookStep8;
use App\Http\Requests\StoryBookStep9;
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


    public function createStep1(StoryBookStep1 $request): RedirectResponse
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

    public function generateStep1(StoryBookStep1 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep1($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep2(StoryBookStep2 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep2($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep3(StoryBookStep3 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep3($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep4(StoryBookStep4 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep4($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep5(StoryBookStep5 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep5($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep6(StoryBookStep6 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep6($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep7(StoryBookStep7 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep7($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep8(StoryBookStep8 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep8($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep9(StoryBookStep9 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep9($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep10(StoryBookStep10 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep10($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep11(StoryBookStep11 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep11($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep12(StoryBookStep12 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep12($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep13(StoryBookStep13 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep13($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep14(StoryBookStep14 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep14($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep15(StoryBookStep15 $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateStep15($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $novel?->slug])->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function generateStep16(StoryBookStep16 $request, string $slug): RedirectResponse
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
