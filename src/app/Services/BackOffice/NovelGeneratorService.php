<?php

namespace App\Services\BackOffice;

use App\Exceptions\AiResponseException;
use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Http\Requests\StoryBookStep1;
use App\Models\AiBrain;
use App\Models\Novel;
use App\Models\NovelChapter;
use App\Models\NovelGeneratorStep;
use App\Services\BackOffice\AiBrainService;
use App\Services\BackOffice\AudienceService;
use App\Services\BackOffice\GenreService;
use App\Services\BackOffice\HuggingFaceApiService;
use App\Services\BackOffice\LanguageService;
use App\Services\BackOffice\NovelChapterService;
use App\Services\BackOffice\NovelGeneratorStepService;
use App\Services\BackOffice\NovelTypeService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelGeneratorService
{
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

    public function createNovelFromFoundation(StoryBookStep1 $request): array
    {
        try {
            $step = $this->foundationStep();
            $aiBrain = $this->aiBrainService->findById($request->input('ai_brain_id'));
            $stepData = $this->runFoundationGeneration($request, $aiBrain);

            $novel = DB::transaction(function () use ($request, $stepData, $step) {
                $novel = new Novel();
                $novel->title = $stepData['title'];
                $novel->sub_title = $stepData['subtitle'];
                $novel->foundation = $stepData['foundation'];

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

            return $this->result('success', 'Novel foundation generated successfully.', $novel);
        } catch (Exception $exception) {
            Log::error('Failed to generate novel foundation.', array_merge(
                [
                    'step'      => AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1,
                    'exception' => $exception->getMessage(),
                ],
                $this->aiFailureContext($exception),
            ));

            return $this->result('error', 'Failed to generate novel foundation. Please try again.', null);
        }
    }

    public function continueGeneration(Novel $novel): array
    {
        $activeStep = null;

        try {
            if ($novel->status === NovelHelper::STATUS_COMPLETE) {
                return $this->result('success', 'This novel is already completed.', $novel);
            }

            if (! in_array($novel->status, [NovelHelper::STATUS_ONGOING, NovelHelper::STATUS_PENDING, NovelHelper::STATUS_FAILED, NovelHelper::STATUS_STOPPED], true)) {
                return $this->result('error', 'This novel cannot be generated in its current state.', $novel);
            }

            if (! $novel->ai_brain_id) {
                return $this->result('error', 'No AI Brain is configured for this novel. Generate the foundation first.', $novel);
            }

            $activeStep = $this->novelGeneratorStepService->nextPendingStep($novel);

            if (! $activeStep) {
                $this->finalizeNovel($novel);

                return $this->result('success', 'Novel completed successfully.', $novel);
            }

            if (in_array($novel->status, [NovelHelper::STATUS_FAILED, NovelHelper::STATUS_STOPPED], true)) {
                $novel->status = NovelHelper::STATUS_ONGOING;
                $novel->save();
            }

            if ($this->isChapterContentStep($activeStep)) {
                $message = $this->continueChapterContents($novel, $activeStep);

                return $this->result('success', $message, $novel);
            }

            $this->novelGeneratorStepService->markStarted($novel, $activeStep);

            $stepData = $this->generateStep($novel, $activeStep);

            DB::transaction(function () use ($novel, $activeStep, $stepData) {
                $this->persistStepData($novel, $activeStep, $stepData);
                $novel->status = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            $this->novelGeneratorStepService->markCompleted($novel, $activeStep);

            return $this->result('success', sprintf('%s generated successfully.', $activeStep->name), $novel);
        } catch (Exception $exception) {
            Log::error('Novel generation failed.', array_merge(
                [
                    'step'      => $activeStep?->name,
                    'exception' => $exception->getMessage(),
                ],
                $this->aiFailureContext($exception),
            ));

            if ($activeStep) {
                $this->novelGeneratorStepService->markFailed($novel, $activeStep, $exception->getMessage());
            }

            $novel->status = NovelHelper::STATUS_FAILED;
            $novel->save();

            return $this->result('error', $this->generationFailureMessage($activeStep), $novel);
        }
    }

    public function stop(Novel $novel): array
    {
        if ($novel->status === NovelHelper::STATUS_COMPLETE) {
            return $this->result('error', 'This novel is already completed.', $novel);
        }

        $novel->status = NovelHelper::STATUS_STOPPED;
        $novel->save();

        return $this->result('success', 'Novel generation stopped. You can resume it later.', $novel);
    }

    private function runFoundationGeneration(StoryBookStep1 $request, AiBrain $aiBrain): array
    {
        $stepName = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1;
        $aiPrompt = $this->foundationStep()->aiPrompt;

        if (! $aiPrompt) {
            throw new Exception('The Foundation Generator AI prompt is not configured.');
        }

        $inputs = [
            'language'               => $this->languageService->findByIdsOrEnglish($request->input('language_id')),
            'audience'               => $this->audienceService->findById($request->input('audience_id')),
            'novel_type'             => $this->novelTypeService->findById($request->input('novel_type_id')),
            'genres'                 => $this->genreService->findByIdsOrRandom($request->input('genre_ids')),
            'additional_information' => $request->input('additional_information', 'Auto'),
        ];

        $formattedInput = $this->huggingFaceApiService->step1InputsFormatter($inputs);
        $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formattedInput);

        return $this->huggingFaceApiService->sendPostRequest($stepName, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);
    }

    private function generateStep(Novel $novel, NovelGeneratorStep $step): array
    {
        $aiBrain = $this->aiBrainService->findById($novel->ai_brain_id);

        if (! $step->aiPrompt) {
            throw new Exception(sprintf('The AI prompt for "%s" is not configured.', $step->name));
        }

        if ($step->name === AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15) {
            $this->novelChapterService->generateSummaries($novel, $step->name, $step->aiPrompt, $aiBrain);

            return [];
        }

        $formatterMethod = $this->inputFormatterMethod($step->name);
        $formattedInput = $this->huggingFaceApiService->{$formatterMethod}($novel);
        $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($step->aiPrompt->prompt, $formattedInput);

        return $this->huggingFaceApiService->sendPostRequest($step->name, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);
    }

    private function continueChapterContents(Novel $novel, NovelGeneratorStep $step): string
    {
        $pendingChapters = $this->chaptersMissingContent($novel);

        if ($pendingChapters->isEmpty()) {
            $this->novelGeneratorStepService->markCompleted($novel, $step);
            $this->finalizeNovel($novel);

            return 'Novel completed successfully.';
        }

        $this->novelGeneratorStepService->markStarted($novel, $step);

        $chapter = $pendingChapters->first();

        $this->generateChapterContent($novel, $chapter, $step);

        return sprintf('Chapter %s content generated successfully.', $chapter->no);
    }

    private function generateChapterContent(Novel $novel, NovelChapter $chapter, NovelGeneratorStep $step): void
    {
        if (! $step->aiPrompt) {
            throw new Exception(sprintf('The AI prompt for "%s" is not configured.', $step->name));
        }

        $aiBrain = $this->aiBrainService->findById($novel->ai_brain_id);

        $this->novelChapterService->generateStep16($novel, $chapter, $step->name, $step->aiPrompt, $aiBrain);
    }

    private function chaptersMissingContent(Novel $novel): Collection
    {
        return $novel->novelChapters()
            ->orderByRaw('CAST(no AS UNSIGNED) ASC')
            ->get()
            ->reject(fn (NovelChapter $chapter) => $this->novelChapterService->hasContent($chapter))
            ->values();
    }

    private function persistStepData(Novel $novel, NovelGeneratorStep $step, array $stepData): void
    {
        match ($step->name) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2  => $novel->characters = $stepData['characters'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3  => $novel->world_bible = $stepData['world_bible'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4  => $novel->locations = $stepData['locations'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5  => $novel->factions = $stepData['factions'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6  => $novel->creatures = $stepData['creatures'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7  => $novel->systems = $stepData['systems'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8  => $novel->timeline = $stepData['timeline'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9  => $novel->story_structure = $stepData['story_structure'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10 => $novel->twists_and_foreshadowing = $stepData['twists_and_foreshadowing'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11 => $novel->scene_plans = $stepData['scene_plans'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12 => $novel->dialogue_plans = $stepData['dialogue_plans'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13 => $novel->chapter_plan = $stepData['chapter_plan'],
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14 => $novel->page_plan = $stepData['page_plan'],
            default                                          => null,
        };
    }

    private function inputFormatterMethod(string $stepName): string
    {
        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2  => 'step2InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3  => 'step3InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4  => 'step4InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5  => 'step5InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6  => 'step6InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7  => 'step7InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8  => 'step8InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9  => 'step9InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10 => 'step10InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11 => 'step11InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12 => 'step12InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13 => 'step13InputsFormatter',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14 => 'step14InputsFormatter',
            default                                          => throw new Exception(sprintf('Unsupported novel generator step [%s].', $stepName)),
        };
    }

    private function foundationStep(): NovelGeneratorStep
    {
        return $this->novelGeneratorStepService->orderedSteps()->first();
    }

    private function isChapterContentStep(NovelGeneratorStep $step): bool
    {
        return $step->name === AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16;
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

        $missingSummaries = collect();
        $missingContents = collect();

        foreach ($chapters as $chapter) {
            if (! $this->novelChapterService->hasSummary($chapter)) {
                $missingSummaries->push((string) $chapter->no);
            }

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

        if ($missingSummaries->isNotEmpty()) {
            $findings[] = 'Missing summaries: ' . $missingSummaries->implode(', ') . '.';
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

    private function aiFailureContext(Exception $exception): array
    {
        if (! $exception instanceof AiResponseException) {
            return [];
        }

        return [
            'ai_failure' => $exception->context(),
            'retryable'  => $exception->isRetryable(),
        ];
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