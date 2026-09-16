<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\NovelFoundationRequest;
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

    public function edit(string $slug): InertiaResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        return Inertia::render('back-office/novels/Create', [
            'novel' => $novel,
        ]);
    }


    public function generateFoundation(NovelFoundationRequest $request): RedirectResponse
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

    public function regenerateFoundation(NovelFoundationRequest $request, string $slug): RedirectResponse
    {
        $novel = $this->novelService->find($slug);
        Gate::authorize('update', $novel);

        $result = $this->novelService->generateFoundation($request, $novel);

        return to_route('back-office.novels.edit', ["slug" => $slug])->with('flash_message', [
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
