<?php
namespace App\Services;

use App\Helpers\NovelGeneratorHelper;
use App\Models\AiPrompt;
use App\Models\NovelGenerator;
use App\Models\NovelGeneratorStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NovelGeneratorStepService
{
    public function new (): NovelGeneratorStep
    {
        return new NovelGeneratorStep();
    }

    public function find(string $slug): NovelGeneratorStep
    {
        return NovelGeneratorStep::with([
            "aiPrompt",
            "novelGeneratorStep",
            "privousNovelGeneratorStep",

            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function search(Request $request, NovelGenerator $novelGenerator)
    {
        $perPage = $request->input('per_page', 10);

        $query = NovelGeneratorStep::query();

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
            ], 'like', $likeSearch);
        }

        $query = $query->where("novel_generator_id", $novelGenerator->id);

        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

    public function saveUsingNovelGenerator(NovelGenerator $novelGenerator, AiPrompt $aiPrompt, ?NovelGeneratorStep $privousNovelGeneratorStep, $input = null, $output = null): NovelGeneratorStep
    {
        $isNew = empty($novelGenerator->id);

        $novelGeneratorStep = $this->new();

        $novelGeneratorStep->name                            = ('Novel Generator Step ' . now()->format('YmdHis'));
        $novelGeneratorStep->novel_generator_id              = $novelGenerator->id;
        $novelGeneratorStep->ai_prompt_id                    = $aiPrompt->id;
        $novelGeneratorStep->privous_novel_generator_step_id = $privousNovelGeneratorStep?->id ?? null;
        $novelGeneratorStep->depend_on_prompt_ids            = $aiPrompt->depend_on_prompt_ids;
        $novelGeneratorStep->status                          = NovelGeneratorHelper::STATUS_COMPLETE;

        $novelGeneratorStep->input  = $input;
        $novelGeneratorStep->outout = $output;

        if ($isNew) {
            $novelGeneratorStep->created_by_id = $novelGeneratorStep->created_by_id ?? Auth::id();
        }

        $novelGeneratorStep->save();

        return $novelGeneratorStep;
    }
}
