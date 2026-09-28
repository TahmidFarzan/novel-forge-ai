<?php

namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Http\Requests\NovelGenerationRequest;
use App\Models\AiBrain;
use App\Models\Novel;
use App\Models\NovelChapter;
use App\Models\NovelGeneratorStep;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelGeneratorService
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR   = 'error';
    public const STATUS_BUSY    = 'busy';

    protected const GENERATION_LOCK_SECONDS = 900;

    protected AiBrainService $aiBrainService;
    protected AudienceService $audienceService;
    protected GenreService $genreService;
    protected NovelTypeService $novelTypeService;
    protected HuggingFaceApiService $huggingFaceApiService;
    protected LanguageService $languageService;
    protected NovelChapterService $novelChapterService;
    protected NovelGeneratorStepService $novelGeneratorStepService;

    public function __construct(AiBrainService $aiBrainService, AudienceService $audienceService, GenreService $genreService, NovelTypeService $novelTypeService, HuggingFaceApiService $huggingFaceApiService, LanguageService $languageService, NovelChapterService $novelChapterService, NovelGeneratorStepService $novelGeneratorStepService)
    {
        $this->aiBrainService            = $aiBrainService;
        $this->audienceService           = $audienceService;
        $this->genreService              = $genreService;
        $this->novelTypeService          = $novelTypeService;
        $this->huggingFaceApiService     = $huggingFaceApiService;
        $this->languageService           = $languageService;
        $this->novelChapterService       = $novelChapterService;
        $this->novelGeneratorStepService = $novelGeneratorStepService;
    }

    public function createNovel(NovelGenerationRequest $request): array
    {
        try {
            $step        = $this->stepByName(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);
            $aiBrain     = $this->aiBrainService->findById($request->input('ai_brain_id'));
            $foundation  = $this->generateFoundation($step, $aiBrain, $this->foundationContext($request->only([
                'language_id',
                'audience_id',
                'novel_type_id',
                'genre_ids',
                'additional_information',
            ])));

            $novel = DB::transaction(function () use ($request, $foundation, $step) {
                $novel = new Novel();

                $this->applyFoundation($novel, $foundation);

                $novel->audience_id = $request->input('audience_id');
                $novel->novel_type_id = $request->input('novel_type_id');
                $novel->language_id = $request->input('language_id');
                $novel->ai_brain_id = $request->input('ai_brain_id');
                $novel->additional_information = $request->input('additional_information');

                $novel->status = NovelHelper::STATUS_ONGOING;
                $novel->datetime = now();
                $novel->created_by_id = Auth::id();
                $novel->generation_steps = $this->novelGeneratorStepService->initializeProgress();

                $novel->save();

                $novel->genres()->sync((array) $request->input('genre_ids', []));

                $this->novelGeneratorStepService->markCompleted($novel, $step);

                return $novel;
            });

            return $this->result(self::STATUS_SUCCESS, 'Foundation generated successfully.', $novel);
        } catch (Exception $exception) {
            Log::error('Failed to generate novel foundation.', [
                'step'      => AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                'exception' => $exception::class,
                'reason'    => $exception->getMessage(),
            ]);

            return $this->result(self::STATUS_ERROR, 'Failed to generate novel foundation. Please try again.', null);
        }
    }

    public function continueGeneration(Novel $novel): array
    {
        $lock = Cache::lock($this->generationLockKey($novel), self::GENERATION_LOCK_SECONDS);

        if (! $lock->get()) {
            return $this->result(self::STATUS_BUSY, 'Generation is already running for this novel.', $novel);
        }

        try {
            return $this->runNextPendingStep($novel->refresh());
        } finally {
            $lock->release();
        }
    }

    private function runNextPendingStep(Novel $novel): array
    {
        $activeStep = null;

        try {
            if ($novel->status === NovelHelper::STATUS_COMPLETE) {
                return $this->result(self::STATUS_SUCCESS, 'This novel is already completed.', $novel);
            }

            if (! in_array($novel->status, [NovelHelper::STATUS_ONGOING, NovelHelper::STATUS_PENDING, NovelHelper::STATUS_FAILED, NovelHelper::STATUS_STOPPED, NovelHelper::STATUS_DRAFT], true)) {
                return $this->result(self::STATUS_ERROR, 'This novel cannot be generated in its current state.', $novel);
            }

            if (! $novel->ai_brain_id) {
                return $this->result(self::STATUS_ERROR, 'No AI Brain is configured for this novel. Generate the foundation first.', $novel);
            }

            $activeStep = $this->novelGeneratorStepService->nextPendingStep($novel);

            if (! $activeStep) {
                $this->finalizeNovel($novel);

                return $this->result(self::STATUS_SUCCESS, 'Novel completed successfully.', $novel);
            }

            if (in_array($novel->status, [NovelHelper::STATUS_FAILED, NovelHelper::STATUS_STOPPED], true)) {
                $novel->status = NovelHelper::STATUS_ONGOING;
                $novel->save();
            }

            $this->novelGeneratorStepService->markStarted($novel, $activeStep);

            $message = $this->runStep($novel, $activeStep);

            $novel->status = NovelHelper::STATUS_ONGOING;
            $novel->save();

            if (! $this->isChapterContentStep($activeStep)) {
                $this->novelGeneratorStepService->markCompleted($novel, $activeStep);

                return $this->result(self::STATUS_SUCCESS, $message, $novel);
            }

            if ($this->chaptersMissingContent($novel)->isNotEmpty()) {
                return $this->result(self::STATUS_SUCCESS, $message, $novel);
            }

            $this->novelGeneratorStepService->markCompleted($novel, $activeStep);

            $this->finalizeNovel($novel);

            return $this->result(self::STATUS_SUCCESS, $message, $novel);
        } catch (Exception $exception) {
            Log::error('Novel generation failed.', [
                'step'      => $activeStep?->name,
                'exception' => $exception::class,
                'reason'    => $exception->getMessage(),
            ]);

            if ($activeStep) {
                $this->novelGeneratorStepService->markFailed($novel, $activeStep, $this->userFacingStepError($exception));
            }

            $novel->status = NovelHelper::STATUS_FAILED;
            $novel->save();

            return $this->result(self::STATUS_ERROR, $this->generationFailureMessage($activeStep), $novel);
        }
    }

    public function stop(Novel $novel): array
    {
        if ($novel->status === NovelHelper::STATUS_COMPLETE) {
            return $this->result(self::STATUS_ERROR, 'This novel is already completed.', $novel);
        }

        $novel->status = NovelHelper::STATUS_STOPPED;
        $novel->save();

        return $this->result(self::STATUS_SUCCESS, 'Novel generation stopped. You can resume it later.', $novel);
    }

    private function runStep(Novel $novel, NovelGeneratorStep $step): string
    {
        if (! $step->aiPrompt) {
            throw new Exception(sprintf('The AI prompt for "%s" is not configured.', $step->name));
        }

        $aiBrain = $this->aiBrainService->findById($novel->ai_brain_id);

        return match ($step->name) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION       => $this->runFoundationStep($novel, $step, $aiBrain),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER     => $this->runPlanChapterStep($novel, $step, $aiBrain),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT  => $this->runChapterContentStep($novel, $step, $aiBrain),
            default                                                 => throw new Exception(sprintf('Unsupported novel generator step [%s].', $step->name)),
        };
    }

    private function runFoundationStep(Novel $novel, NovelGeneratorStep $step, AiBrain $aiBrain): string
    {
        $foundation = $this->generateFoundation($step, $aiBrain, $this->foundationContext([
            'language_id' => $novel->language_id,
            'audience_id' => $novel->audience_id,
            'novel_type_id' => $novel->novel_type_id,
            'genre_ids' => $novel->genres->pluck('id')->all(),
            'additional_information' => $novel->additional_information,
        ]));

        DB::transaction(function () use ($novel, $foundation) {
            $this->applyFoundation($novel, $foundation);

            $novel->save();
        });

        return 'Foundation generated successfully.';
    }

    private function runPlanChapterStep(Novel $novel, NovelGeneratorStep $step, AiBrain $aiBrain): string
    {
        $plan = $this->huggingFaceApiService->generate(
            $step->name,
            $aiBrain->api_url,
            $aiBrain->api_key,
            $aiBrain->model,
            $step->aiPrompt->prompt,
            $this->huggingFaceApiService->planChapterInputs($novel),
            $aiBrain->max_output_tokens,
            $aiBrain->timeout_seconds,
        );

        DB::transaction(function () use ($novel, $plan) {
            $novel->story_structure = $plan['story_structure'];
            $novel->twists_and_foreshadowing = $plan['twists_and_foreshadowing'];
            $novel->scene_plans = $plan['scene_plans'];
            $novel->dialogue_plans = $plan['dialogue_plans'];
            $novel->chapter_plan = $plan['chapter_plan'];
            $novel->page_plan = $plan['page_plan'];

            $novel->save();

            $this->novelChapterService->syncChaptersFromPlan($novel, $plan['chapter_plan'], $plan['chapter_summaries']);
        });

        return 'Plan chapter generated successfully.';
    }

    private function runChapterContentStep(Novel $novel, NovelGeneratorStep $step, AiBrain $aiBrain): string
    {
        $chapter = $this->chaptersMissingContent($novel)->first();

        if (! $chapter) {
            return 'Novel completed successfully.';
        }

        $this->novelChapterService->generateChapterContent($novel, $chapter, $step->name, $step->aiPrompt, $aiBrain);

        return sprintf('Chapter %s content generated successfully.', $chapter->no);
    }

    private function generateFoundation(NovelGeneratorStep $step, AiBrain $aiBrain, array $context): array
    {
        if (! $step->aiPrompt) {
            throw new Exception('The Foundation AI prompt is not configured.');
        }

        return $this->huggingFaceApiService->generate(
            $step->name,
            $aiBrain->api_url,
            $aiBrain->api_key,
            $aiBrain->model,
            $step->aiPrompt->prompt,
            $this->huggingFaceApiService->foundationInputs($context),
            $aiBrain->max_output_tokens,
            $aiBrain->timeout_seconds,
        );
    }

    private function applyFoundation(Novel $novel, array $foundation): void
    {
        $novel->title = $foundation['title'];
        $novel->sub_title = $foundation['sub_title'];
        $novel->foundation = $foundation['foundation'];
        $novel->characters = $foundation['characters'];
        $novel->world_bible = $foundation['world_bible'];
        $novel->locations = $foundation['locations'];
        $novel->factions = $foundation['factions'];
        $novel->creatures = $foundation['creatures'];
        $novel->systems = $foundation['systems'];
        $novel->timeline = $foundation['timeline'];
    }

    private function foundationContext(array $attributes): array
    {
        return [
            'language' => $this->languageService->findByIdsOrEnglish($attributes['language_id'] ?? null),
            'audience' => $this->audienceService->findById($attributes['audience_id'] ?? null),
            'novel_type' => $this->novelTypeService->findById($attributes['novel_type_id'] ?? null),
            'genres' => $this->genreService->findByIdsOrRandom((array) ($attributes['genre_ids'] ?? [])),
            'additional_information' => $attributes['additional_information'] ?? 'Auto',
        ];
    }

    private function chaptersMissingContent(Novel $novel): Collection
    {
        return $novel->novelChapters()
            ->orderByRaw('CAST(no AS UNSIGNED) ASC')
            ->get()
            ->reject(fn (NovelChapter $chapter) => $this->novelChapterService->hasContent($chapter))
            ->values();
    }

    private function stepByName(string $stepName): NovelGeneratorStep
    {
        return $this->novelGeneratorStepService->stepByName($stepName)
            ?? throw new Exception(sprintf('The novel generator step [%s] is not configured.', $stepName));
    }

    private function isChapterContentStep(NovelGeneratorStep $step): bool
    {
        return $step->name === AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT;
    }

    private function finalizeNovel(Novel $novel): void
    {
        $findings = $this->reviewFindings($novel);

        if ($findings !== []) {
            throw new Exception('Novel review incomplete. ' . implode(' ', $findings));
        }

        $novel->status = NovelHelper::STATUS_COMPLETE;
        $novel->save();
    }

    private function reviewFindings(Novel $novel): array
    {
        $chapterPlan = $novel->chapter_plan ?? [];

        if (! is_array($chapterPlan) || empty($chapterPlan)) {
            return ['Novel has no chapter plan.'];
        }

        $chapters = $novel->novelChapters()
            ->orderByRaw('CAST(no AS UNSIGNED) ASC')
            ->get();

        if ($chapters->isEmpty()) {
            return ['No novel chapters found.'];
        }

        $expectedNumbers = collect($chapterPlan)
            ->map(fn ($entry) => (string) ($entry['chapter_number'] ?? ''))
            ->filter(fn ($no) => $no !== '')
            ->values();

        $orderedExpectedNumbers = $expectedNumbers->implode(',');
        $sortedExpectedNumbers = $expectedNumbers
            ->sortBy(fn ($no) => (int) $no, SORT_REGULAR)
            ->values()
            ->implode(',');

        $actualNumbers = $chapters->map(fn ($chapter) => (string) $chapter->no)->values();

        $missingChapters = $expectedNumbers
            ->diff($actualNumbers)
            ->unique()
            ->values();

        $duplicateChapters = $chapters
            ->groupBy('no')
            ->filter(fn ($group) => $group->count() > 1)
            ->keys()
            ->map(fn ($no) => (string) $no)
            ->values();

        $extraChapters = $actualNumbers
            ->diff($expectedNumbers)
            ->unique()
            ->values();

        $missingContents = collect();

        foreach ($chapters as $chapter) {
            if (! $this->novelChapterService->hasContent($chapter)) {
                $missingContents->push((string) $chapter->no);
            }
        }

        $findings = [];

        if ($orderedExpectedNumbers !== $sortedExpectedNumbers) {
            $findings[] = 'Chapter plan ordering is invalid.';
        }

        if ($missingChapters->isNotEmpty()) {
            $findings[] = 'Missing chapters: ' . $missingChapters->implode(', ') . '.';
        }

        if ($missingContents->isNotEmpty()) {
            $findings[] = 'Missing contents: ' . $missingContents->implode(', ') . '.';
        }

        if ($duplicateChapters->isNotEmpty()) {
            $findings[] = 'Duplicate chapters: ' . $duplicateChapters->implode(', ') . '.';
        }

        if ($extraChapters->isNotEmpty()) {
            $findings[] = 'Unexpected chapters: ' . $extraChapters->implode(', ') . '.';
        }

        return $findings;
    }

    private function generationFailureMessage(?NovelGeneratorStep $step): string
    {
        return $step
            ? sprintf('Generation failed during "%s". Review the step and retry.', $step->name)
            : 'Novel generation failed. Please try again.';
    }

    private function generationLockKey(Novel $novel): string
    {
        return sprintf('novel:%s:generation', $novel->getKey());
    }

    private function userFacingStepError(Exception $exception): string
    {
        return $exception->getMessage();
    }

    private function result(string $status, string $message, ?Novel $novel): array
    {
        return [
            'status'  => $status,
            'message' => $message,
            'novel'   => $novel,
        ];
    }
}
