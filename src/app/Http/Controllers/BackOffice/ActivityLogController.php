<?php

namespace App\Http\Controllers\BackOffice;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Services\BackOffice\ActivityLogService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request): InertiaResponse
    {
        $showSubjectType = true;

        $activityLogs = $this->activityLogService->search(null, null, $request);

        return Inertia::render('back-office/activity-logs/Index', [
            'activityLogs' => $activityLogs,
            'showSubjectType' => $showSubjectType,
        ]);
    }

    public function indexForModel(?string $modelSlug = null, ?string $recordSlug = null, Request $request): InertiaResponse
    {
        $showSubjectType = false;

        $activityLogs = $this->activityLogService->search($modelSlug, $recordSlug, $request);

        return Inertia::render('back-office/activity-logs/Index', [
            'activityLogs' => $activityLogs,
            'showSubjectType' => $showSubjectType,
        ]);
    }

    public function details(string $slug): InertiaResponse
    {
        $activityLog = $this->activityLogService->findBySlug($slug);

        return Inertia::render('back-office/activity-logs/Details', [
            'activityLog' => $activityLog,
        ]);
    }

    public function delete(string $slug): RedirectResponse
    {
        $activityLog = $this->activityLogService->findBySlug($slug);

        if(!Auth::user()->is_super_admin){
            if(! ($activityLog->causer_id == Auth::user()->id)){
                abort("401", "Unauthorise Access");
            }
        }
        $result = $this->activityLogService->delete($activityLog);

        return to_route('back-office.activity-logs.index')->with('flash_message', [
            'message' => $result['message'],
            'status' => $result['status'],
        ]);
    }
}
