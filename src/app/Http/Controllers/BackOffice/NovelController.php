<?php
namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\NovelRequest;
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


    public function save(NovelRequest $request): RedirectResponse
    {
        $novel = $this->novelService->new();
        Gate::authorize('create', $novel);

        $result = $this->novelService->save($request, $novel);

        return to_route('back-office.novels.index')->with('flash_message', [
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
