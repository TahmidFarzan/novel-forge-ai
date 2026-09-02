<?php
namespace App\Http\Controllers;

use App\Services\KdpLayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class KdpLayoutController extends Controller
{
    protected KdpLayoutService $kdpLayoutService;

    public function __construct(KdpLayoutService $kdpLayoutService)
    {
        $this->kdpLayoutService = $kdpLayoutService;
    }

    public function index(Request $request): InertiaResponse
    {
        $kdpLayout = $this->kdpLayoutService->new();

        Gate::authorize('viewAny', $kdpLayout);

        return Inertia::render('kdp-layouts/Index', [
            'kdpLayouts' => $this->kdpLayoutService->search($request),
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $kdpLayout = $this->kdpLayoutService->find($slug);

        Gate::authorize('view', $kdpLayout);

        return Inertia::render('kdp-layouts/Details', [
            'kdpLayout' => $kdpLayout,
        ]);
    }
}
