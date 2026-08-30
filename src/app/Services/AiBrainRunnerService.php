<?php
namespace App\Services;

use App\Models\AiBrainRunner;
use Illuminate\Http\Request;

class AiBrainRunnerService
{
    public function new (): AiBrainRunner
    {
        return new AiBrainRunner();
    }

    public function find(string $slug): AiBrainRunner
    {
        return AiBrainRunner::with([
            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = AiBrainRunner::query();

        if ($request->filled('created_by_id')) {
            $query->where('created_by_id', $request->input('created_by_id'));
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $date = is_string($date) ? new \DateTime($date) : $date;
            $query->whereDate('created_at', '<=', $date);
        }

        if ($request->filled('search')) {
            $search     = $request->input('search');
            $likeSearch = "%{$search}%";

            $query->whereAny([
                'name',
                'brief',
                "url",
            ], 'like', $likeSearch);
        }
        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

}
