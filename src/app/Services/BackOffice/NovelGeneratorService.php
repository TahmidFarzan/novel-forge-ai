<?php

namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Http\Requests\StoryBookStep1;
use App\Http\Requests\StoryBookStep10;
use App\Http\Requests\StoryBookStep11;
use App\Http\Requests\StoryBookStep12;
use App\Http\Requests\StoryBookStep13;
use App\Http\Requests\StoryBookStep14;
use App\Http\Requests\StoryBookStep15;
use App\Http\Requests\StoryBookStep16;
use App\Http\Requests\StoryBookStep2;
use App\Http\Requests\StoryBookStep3;
use App\Http\Requests\StoryBookStep4;
use App\Http\Requests\StoryBookStep5;
use App\Http\Requests\StoryBookStep6;
use App\Http\Requests\StoryBookStep7;
use App\Http\Requests\StoryBookStep8;
use App\Http\Requests\StoryBookStep9;
use App\Models\Novel;
use App\Services\BackOffice\AiBrainService;
use App\Services\BackOffice\AiPromptService;
use App\Services\BackOffice\AudienceService;
use App\Services\BackOffice\GenreService;
use App\Services\BackOffice\HuggingFaceApiService;
use App\Services\BackOffice\LanguageService;
use App\Services\BackOffice\NovelChapterService;
use App\Services\BackOffice\NovelTypeService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NovelGeneratorService
{
    protected AiBrainService $aiBrainService;
    protected AiPromptService $aiPromptService;
    protected AudienceService $audienceService;
    protected GenreService $genreService;
    protected NovelTypeService $novelTypeService;
    protected HuggingFaceApiService $huggingFaceApiService;
    protected LanguageService $languageService;
    protected NovelChapterService $novelChapterService;

    public function __construct(AiBrainService $aiBrainService, AiPromptService $aiPromptService, AudienceService $audienceService, GenreService $genreService, NovelTypeService $novelTypeService, HuggingFaceApiService $huggingFaceApiService, LanguageService $languageService, NovelChapterService $novelChapterService)
    {
        $this->aiBrainService        = $aiBrainService;
        $this->aiPromptService       = $aiPromptService;
        $this->audienceService       = $audienceService;
        $this->genreService          = $genreService;
        $this->novelTypeService      = $novelTypeService;
        $this->huggingFaceApiService = $huggingFaceApiService;
        $this->languageService       = $languageService;
        $this->novelChapterService   = $novelChapterService;
    }

    public function generateStep1(StoryBookStep1 $request, Novel $novel)
    {
        $isNew       = empty($novel->id);
        $statusEvent = $isNew ? "save" : "update";

        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "language"               => $this->languageService->findByIdsOrEnglish($request->input("language_id")),
                "audience"               => $this->audienceService->findById($request->input("audience_id")),
                "novel_type"             => $this->novelTypeService->findById($request->input("novel_type_id")),
                "genres"                 => $this->genreService->findByIdsOrRandom($request->input("genre_ids")),
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $formatedInput = $this->huggingFaceApiService->step1InputsFormatter($inputs);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to {$statusEvent} novel.", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep2(StoryBookStep2 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));


            $formatedInput = $this->huggingFaceApiService->step2InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;

        } catch (Exception $exception) {

            Log::error("Failed to generate Story characters", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep3(StoryBookStep3 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));


            $formatedInput = $this->huggingFaceApiService->step3InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate World Bible", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep4(StoryBookStep4 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step4InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Locations", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep5(StoryBookStep5 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step5InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Factions", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep6(StoryBookStep6 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step6InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Creatures", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep7(StoryBookStep7 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step7InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Systems", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep8(StoryBookStep8 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step8InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Timeline", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep9(StoryBookStep9 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step9InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Story structure", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep10(StoryBookStep10 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step10InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Twists and foreshadowing", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep11(StoryBookStep11 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step11InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Scene plans", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep12(StoryBookStep12 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step12InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Dialogue plans", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep13(StoryBookStep13 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step13InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Chapter plan", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep14(StoryBookStep14 $request, Novel $novel)
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $formatedInput = $this->huggingFaceApiService->step14InputsFormatter($novel);
            $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
            $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            return $stepData;
        } catch (Exception $exception) {

            Log::error("Failed to generate Page plan", [
                "exception" => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function generateStep15(StoryBookStep15 $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $this->novelChapterService->generateSummaries($novel, $step, $aiPrompt, $aiBrain);

            return [
                'status'  => 'success',
                'message' => 'Chapter summaries generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Chapter summaries", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Chapter summaries. Please try again.',
            ];
        }
    }

    public function generateStep16(StoryBookStep16 $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16;

            $chapterNo = $request->input("chapter_no");

            $novelChapter = $this->novelChapterService->findByNo($novel, $chapterNo);

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $this->novelChapterService->generateStep16($novel, $novelChapter, $step, $aiPrompt, $aiBrain);

            return [
                'status'  => 'success',
                'message' => 'Chapter ' . $chapterNo . ' content generated successfully.',
                'chapter' => $novelChapter,
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Chapter content", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Chapter content. Please try again.',
            ];
        }
    }

    public function reviewNovel(Novel $novel): array
    {
        $chapterPlan = $novel->chapter_plan ?? [];

        if (! is_array($chapterPlan) || empty($chapterPlan)) {
            return [
                'status'  => 'error',
                'message' => 'Novel has no chapter plan. Generate the chapter plan first.',
            ];
        }

        $chapters = $novel->novelChapters()
            ->orderByRaw('CAST(no AS UNSIGNED) ASC')
            ->get();

        if ($chapters->isEmpty()) {
            return [
                'status'  => 'error',
                'message' => 'No novel chapters found. Generate chapter summaries first.',
            ];
        }

        $expectedNumbers = collect($chapterPlan)
            ->map(fn($entry) => (string) ($entry['chapter_number'] ?? ''))
            ->filter(fn($no) => $no !== '')
            ->values();

        $orderedExpectedNumbers = $expectedNumbers->implode(',');
        $sortedExpectedNumbers  = $expectedNumbers
            ->sortBy(fn($no) => (int) $no, SORT_REGULAR)
            ->values()
            ->implode(',');

        $orderingInvalid = $orderedExpectedNumbers !== $sortedExpectedNumbers;

        $actualNumbers = $chapters->map(fn($chapter) => (string) $chapter->no)->values();

        $missingChapters = $expectedNumbers
            ->diff($actualNumbers)
            ->unique()
            ->values();

        $duplicateChapters = $chapters
            ->groupBy('no')
            ->filter(fn($group) => $group->count() > 1)
            ->keys()
            ->map(fn($no) => (string) $no)
            ->values();

        $extraChapters = $actualNumbers
            ->diff($expectedNumbers)
            ->unique()
            ->values();

        $missingSummaries = collect();
        $missingContents  = collect();

        foreach ($chapters as $chapter) {
            if (! $this->novelChapterService->hasSummary($chapter)) {
                $missingSummaries->push((string) $chapter->no);
            }

            if (! $this->novelChapterService->hasContent($chapter)) {
                $missingContents->push((string) $chapter->no);
            }
        }

        if (
            $orderingInvalid ||
            $missingChapters->isNotEmpty() ||
            $duplicateChapters->isNotEmpty() ||
            $extraChapters->isNotEmpty() ||
            $missingSummaries->isNotEmpty() ||
            $missingContents->isNotEmpty()
        ) {
            return [
                'status'  => 'error',
                'message' => $this->reviewFindingsMessage($orderingInvalid, $missingChapters, $missingSummaries, $missingContents, $duplicateChapters, $extraChapters),
            ];
        }

        $novel = DB::transaction(function () use ($novel) {
            $novel->status = NovelHelper::STATUS_COMPLETE;
            $novel->save();

            return $novel;
        });

        return [
            'status'  => 'success',
            'message' => 'Novel completed successfully.',
            'novel'   => $novel,
        ];
    }

    private function reviewFindingsMessage(bool $orderingInvalid, $missingChapters, $missingSummaries, $missingContents, $duplicateChapters, $extraChapters): string
    {
        $findings = [];

        if ($orderingInvalid) {
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

        return 'Novel review incomplete. ' . implode(' ', $findings);
    }
}
