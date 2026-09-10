<?php
namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\NovelTypeRequest;
use App\Services\BackOffice\NovelTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NovelTypeController extends Controller
{
    protected NovelTypeService $novelTypeService;

    public function __construct(NovelTypeService $novelTypeService)
    {
        $this->novelTypeService = $novelTypeService;
    }

    public function index(Request $request): InertiaResponse
    {
        $novelType = $this->novelTypeService->new();
        Gate::authorize('viewAny', $novelType);

        $novelTypes = $this->novelTypeService->search($request);

        return Inertia::render('back-office/novel-types/Index', [
            'novelTypes' => $novelTypes,
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $novelType = $this->novelTypeService->find($slug);

        Gate::authorize('view', $novelType);

        return Inertia::render('back-office/novel-types/Details', [
            'novelType' => $novelType,
        ]);
    }

    public function create(): InertiaResponse
    {
        $novelType = $this->novelTypeService->new();
        Gate::authorize('create', $novelType);

        return Inertia::render('back-office/novel-types/Create', [
            'novelType' => $novelType,
        ]);
    }

    public function edit(string $slug): InertiaResponse
    {
        $novelType = $this->novelTypeService->find($slug);

        Gate::authorize('update', $novelType);

        return Inertia::render('back-office/novel-types/Create', [
            'novelType' => $novelType,
        ]);
    }

    public function save(NovelTypeRequest $request): RedirectResponse
    {
        $novelType = $this->novelTypeService->new();
        Gate::authorize('create', $novelType);

        $result = $this->novelTypeService->save($request, $novelType);

        return to_route('back-office.novel-types.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function update(NovelTypeRequest $request, string $slug): RedirectResponse
    {
        $novelType = $this->novelTypeService->find($slug);

        Gate::authorize('update', $novelType);

        $result = $this->novelTypeService->save($request, $novelType);

        return to_route('back-office.novel-types.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

    public function delete(string $slug): RedirectResponse
    {
        $novelType = $this->novelTypeService->find($slug);

        Gate::authorize('delete', $novelType);

        $result = $this->novelTypeService->delete($novelType);

        return to_route('back-office.novel-types.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }
}