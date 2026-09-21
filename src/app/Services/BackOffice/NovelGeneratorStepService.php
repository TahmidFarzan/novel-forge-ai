<?php

namespace App\Services\BackOffice;

use App\Models\NovelGeneratorStep;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NovelGeneratorStepService
{
    public function new(): NovelGeneratorStep
    {
        return new NovelGeneratorStep;
    }

    public function find(string $slug): NovelGeneratorStep
    {
        return NovelGeneratorStep::with([
            'aiPrompt',
            'previousStep',
            'nextStep',
            'createdBy',

            'activityLogs' => fn ($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = NovelGeneratorStep::query()->with([
            'aiPrompt',
            'previousStep',
            'nextStep',
        ]);

        if ($request->filled('created_by_id')) {
            $query->where('created_by_id', $request->input('created_by_id'));
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $date = is_string($date) ? new \DateTime($date) : $date;
            $query->whereDate('created_at', '<=', $date);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $likeSearch = "%{$search}%";

            $query->whereAny([
                'name',
                'slug',
            ], 'like', $likeSearch);
        }

        $novelGeneratorSteps = $query->orderBy('id')
            ->paginate($perPage)
            ->appends($request->all());

        $this->loadDependencySteps($novelGeneratorSteps->getCollection());

        return $novelGeneratorSteps;
    }

    private function loadDependencySteps(Collection $novelGeneratorSteps): void
    {
        $dependOnStepIds = $novelGeneratorSteps
            ->flatMap(fn (NovelGeneratorStep $novelGeneratorStep) => $novelGeneratorStep->dependencyStepIds())
            ->unique()
            ->values();

        if ($dependOnStepIds->isEmpty()) {
            return;
        }

        $dependencySteps = NovelGeneratorStep::query()
            ->whereIn('id', $dependOnStepIds)
            ->get()
            ->keyBy('id');

        $novelGeneratorSteps->each(function (NovelGeneratorStep $novelGeneratorStep) use ($dependencySteps) {
            $novelGeneratorStep->setRelation(
                'dependencySteps',
                collect($novelGeneratorStep->dependencyStepIds())
                    ->map(fn (int $stepId) => $dependencySteps->get($stepId))
                    ->filter()
                    ->values()
            );
        });
    }
}
