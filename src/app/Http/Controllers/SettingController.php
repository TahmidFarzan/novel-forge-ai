<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SettingController extends Controller
{
    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index(Request $request): InertiaResponse
    {

        $setting = $this->settingService->new();
        Gate::authorize('viewAny', $setting);

        $settings = $this->settingService->search($request);

        return Inertia::render('settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $setting = $this->settingService->find($slug);

        Gate::authorize('view', $setting);

        return Inertia::render('settings/Details', [
            'setting' => $setting,
        ]);
    }

    public function edit(string $slug): InertiaResponse
    {
        $setting = $this->settingService->find($slug);

        Gate::authorize('update', $setting);

        return Inertia::render('settings/Create', [
            'setting' => $setting,
        ]);
    }

    public function update(SettingRequest $request, string $slug): RedirectResponse
    {
        $setting = $this->settingService->find($slug);

        Gate::authorize('update', $setting);

        $result = $this->settingService->save($request, $setting);

        return to_route('settings.index')->with('flash_message', [
            'message' => $result['message'],
            'status'  => $result['status'],
        ]);
    }

}
