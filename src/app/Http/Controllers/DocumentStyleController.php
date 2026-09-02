<?php
namespace App\Http\Controllers;

use App\Services\DocumentStyleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DocumentStyleController extends Controller
{
    protected DocumentStyleService $documentStyleService;

    public function __construct(DocumentStyleService $documentStyleService)
    {
        $this->documentStyleService = $documentStyleService;
    }

    public function index(Request $request): InertiaResponse
    {
        $documentStyle = $this->documentStyleService->new();

        Gate::authorize('viewAny', $documentStyle);

        return Inertia::render('document-styles/Index', [
            'documentStyles' => $this->documentStyleService->search($request),
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $documentStyle = $this->documentStyleService->find($slug);

        Gate::authorize('view', $documentStyle);

        return Inertia::render('document-styles/Details', [
            'documentStyle' => $documentStyle,
        ]);
    }
}
