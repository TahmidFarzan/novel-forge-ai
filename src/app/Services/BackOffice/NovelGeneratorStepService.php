<?php

namespace App\Services\BackOffice;

use App\Models\Novel;
use App\Models\NovelGeneratorStep;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NovelGeneratorStepService
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';

    public function new(): NovelGeneratorStep
    {
        return new NovelGeneratorStep;
    }

    public function orderedSteps(): Collection
    {
        return NovelGeneratorStep::query()
            ->with('aiPrompt')
            ->orderBy('id')
            ->get();
    }

    public function initializeProgress(): array
    {
        $progress = [];

        foreach ($this->orderedSteps() as $step) {
            $progress[$step->id] = $this->emptyProgressState();
        }

        return $progress;
    }

    public function progress(Novel $novel): array
    {
        $progress = $novel->generation_steps;

        return is_array($progress) ? $progress : [];
    }

    public function stepProgress(Novel $novel, NovelGeneratorStep $step): array
    {
        $progress = $this->progress($novel);

        return $progress[$step->id] ?? $this->emptyProgressState();
    }

    public function nextPendingStep(Novel $novel): ?NovelGeneratorStep
    {
        foreach ($this->orderedSteps() as $step) {
            $state = $this->stepProgress($novel, $step);

            if (($state['status'] ?? self::STATUS_PENDING) !== self::STATUS_COMPLETED) {
                return $step;
            }
        }

        return null;
    }

    public function markStarted(Novel $novel, NovelGeneratorStep $step): void
    {
        $this->updateStepProgress($novel, $step, [
            'status' => self::STATUS_IN_PROGRESS,
            'error' => null,
        ]);
    }

    public function markCompleted(Novel $novel, NovelGeneratorStep $step): void
    {
        $this->updateStepProgress($novel, $step, [
            'status' => self::STATUS_COMPLETED,
            'finished_at' => now()->toISOString(),
            'error' => null,
        ]);
    }

    public function markFailed(Novel $novel, NovelGeneratorStep $step, string $error): void
    {
        $this->updateStepProgress($novel, $step, [
            'status' => self::STATUS_FAILED,
            'finished_at' => now()->toISOString(),
            'error' => $error,
        ]);
    }

    public function completedStepCount(Novel $novel): int
    {
        return collect($this->progress($novel))
            ->filter(fn (array $state) => ($state['status'] ?? null) === self::STATUS_COMPLETED)
            ->count();
    }

    public function allStepsCompleted(Novel $novel): bool
    {
        $totalSteps = $this->orderedSteps()->count();

        if ($totalSteps === 0) {
            return false;
        }

        return $this->completedStepCount($novel) >= $totalSteps;
    }

    private function updateStepProgress(Novel $novel, NovelGeneratorStep $step, array $changes): void
    {
        $progress = $this->progress($novel);
        $current = $progress[$step->id] ?? $this->emptyProgressState();

        if (($changes['status'] ?? null) === self::STATUS_IN_PROGRESS && ($current['started_at'] ?? null) !== null) {
            unset($changes['started_at']);
        }

        if (($changes['status'] ?? null) !== self::STATUS_IN_PROGRESS && ($current['started_at'] ?? null) === null) {
            $changes['started_at'] = now()->toISOString();
        }

        $progress[$step->id] = array_merge($this->emptyProgressState(), $current, $changes);

        $novel->generation_steps = $progress;
        $novel->save();
    }

    private function emptyProgressState(): array
    {
        return [
            'status' => self::STATUS_PENDING,
            'started_at' => null,
            'finished_at' => null,
            'error' => null,
        ];
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
