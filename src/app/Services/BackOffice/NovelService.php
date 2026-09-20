<?php
namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Http\Requests\NovelChapterContentRequest;
use App\Http\Requests\NovelChapterPlannerRequest;
use App\Http\Requests\NovelChapterSummaryRequest;
use App\Http\Requests\NovelCharactersRequest;
use App\Http\Requests\NovelCreaturesRequest;
use App\Http\Requests\NovelDialoguePlannerRequest;
use App\Http\Requests\NovelFactionsRequest;
use App\Http\Requests\NovelFoundationRequest;
use App\Http\Requests\NovelLocationsRequest;
use App\Http\Requests\NovelPagePlannerRequest;
use App\Http\Requests\NovelScenePlannerRequest;
use App\Http\Requests\NovelStoryStructureRequest;
use App\Http\Requests\NovelSystemsRequest;
use App\Http\Requests\NovelTimelineRequest;
use App\Http\Requests\NovelTwistsAndForeshadowingRequest;
use App\Http\Requests\NovelWorldBibleRequest;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NovelService
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

    public function new (): Novel
    {
        return new Novel();
    }

    public function find(string $slug): Novel
    {
        return Novel::with([
            'language',
            'novelType',
            'audience',
            'genres',

            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',

            'novelChapters',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Novel::query();

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
                'title',
                'sub_title',
            ], 'like', $likeSearch);
        }

        if ($request->filled('genre_id')) {
            $query->whereHas(
                'genres',
                fn($query) => $query->where('genres.id', $request->input('genre_id'))
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

    public function generateStep1(NovelFoundationRequest $request, Novel $novel): array
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

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            $novel = DB::transaction(function () use ($request, $stepData, $novel, $isNew) {
                $novel->title      = $stepData['title'];
                $novel->sub_title  = $stepData['subtitle'];
                $novel->foundation = $stepData['foundation'];

                $novel->audience_id   = $request->input("audience_id");
                $novel->novel_type_id = $request->input("novel_type_id");
                $novel->language_id   = $request->input("language_id");

                $novel->additional_information = $request->input("additional_information");

                $novel->status = NovelHelper::STATUS_ONGOING;

                if ($isNew) {
                    $novel->datetime      = now();
                    $novel->created_by_id = Auth::id();
                }

                $novel->save();

                if ($request->has('genre_ids')) {
                    $novel->genres()->sync((array) $request->input('genre_ids', []));
                }

                return $novel;
            });

            return [
                "novel"   => $novel,
                'status'  => 'success',
                'message' => $isNew
                    ? 'Novel created successfully.'
                    : 'Novel updated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to {$statusEvent} novel.", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                "novel"   => null,
                'status'  => 'error',
                'message' => 'Failed to save novel. Please try again.',
            ];
        }
    }

    public function generateStep2(NovelCharactersRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "foundation"             => $novel->foundation ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->characters = $stepData['characters'];
                $novel->status     = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Story characters generate successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Story characters", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Story characters. Please try again.',
            ];
        }
    }

    public function generateStep3(NovelWorldBibleRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "foundation"             => $novel->foundation ?? [],
                "characters"             => $novel->characters ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->world_bible = $stepData['world_bible'];
                $novel->status      = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'World Bible generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate World Bible", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate World Bible. Please try again.',
            ];
        }
    }

    public function generateStep4(NovelLocationsRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "world_bible"            => $novel->world_bible ?? [],
                "characters"             => $novel->characters ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->locations = $stepData['locations'];
                $novel->status    = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Locations generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Locations", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Locations. Please try again.',
            ];
        }
    }

    public function generateStep5(NovelFactionsRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "world_bible"            => $novel->world_bible ?? [],
                "locations"              => $novel->locations ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->factions = $stepData['factions'];
                $novel->status   = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Factions generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Factions", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Factions. Please try again.',
            ];
        }
    }

    public function generateStep6(NovelCreaturesRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "world_bible"            => $novel->world_bible ?? [],
                "locations"              => $novel->locations ?? [],
                "factions"               => $novel->factions ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->creatures = $stepData['creatures'];
                $novel->status    = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Creatures generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Creatures", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Creatures. Please try again.',
            ];
        }
    }

    public function generateStep7(NovelSystemsRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "world_bible"            => $novel->world_bible ?? [],
                "creatures"              => $novel->creatures ?? [],
                "factions"               => $novel->factions ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->systems = $stepData['systems'];
                $novel->status  = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Systems generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Systems", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Systems. Please try again.',
            ];
        }
    }

    public function generateStep8(NovelTimelineRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "world_bible"            => $novel->world_bible ?? [],
                "factions"               => $novel->factions ?? [],
                "foundation"             => $novel->foundation ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->timeline = $stepData['timeline'];
                $novel->status   = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Timeline generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Timeline", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Timeline. Please try again.',
            ];
        }
    }

    public function generateStep9(NovelStoryStructureRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "foundation"             => $novel->foundation ?? [],
                "characters"             => $novel->characters ?? [],
                "world_bible"            => $novel->world_bible ?? [],
                "timeline"               => $novel->timeline ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->story_structure = $stepData['story_structure'];
                $novel->status          = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Story structure generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Story structure", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Story structure. Please try again.',
            ];
        }
    }

    public function generateStep10(NovelTwistsAndForeshadowingRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "story_structure"        => $novel->story_structure ?? [],
                "characters"             => $novel->characters ?? [],
                "world_bible"            => $novel->world_bible ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->twists_and_foreshadowing = $stepData['twists_and_foreshadowing'];
                $novel->status                   = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Twists and foreshadowing generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Twists and foreshadowing", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Twists and foreshadowing. Please try again.',
            ];
        }
    }

    public function generateStep11(NovelScenePlannerRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "story_structure"          => $novel->story_structure ?? [],
                "twists_and_foreshadowing" => $novel->twists_and_foreshadowing ?? [],
                "locations"                => $novel->locations ?? [],
                "additional_information"   => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->scene_plans = $stepData['scene_plans'];
                $novel->status      = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Scene plans generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Scene plans", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Scene plans. Please try again.',
            ];
        }
    }

    public function generateStep12(NovelDialoguePlannerRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "characters"             => $novel->characters ?? [],
                "scene_plans"            => $novel->scene_plans ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->dialogue_plans = $stepData['dialogue_plans'];
                $novel->status         = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Dialogue plans generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Dialogue plans", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Dialogue plans. Please try again.',
            ];
        }
    }

    public function generateStep13(NovelChapterPlannerRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "scene_plans"            => $novel->scene_plans ?? [],
                "story_structure"        => $novel->story_structure ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->chapter_plan = $stepData['chapter_plan'];
                $novel->status       = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Chapter plan generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Chapter plan", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Chapter plan. Please try again.',
            ];
        }
    }

    public function generateStep14(NovelPagePlannerRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $inputs = [
                "chapter_plan"           => $novel->chapter_plan ?? [],
                "scene_plans"            => $novel->scene_plans ?? [],
                "additional_information" => $request->input("additional_information", "Auto"),
            ];

            $stepData = $this->huggingFaceApiService->generateStepData($step, $aiPrompt->prompt, $inputs, $aiBrain);

            DB::transaction(function () use ($stepData, $novel) {
                $novel->page_plan = $stepData['page_plan'];
                $novel->status    = NovelHelper::STATUS_ONGOING;
                $novel->save();
            });

            return [
                'status'  => 'success',
                'message' => 'Page plan generated successfully.',
            ];
        } catch (Exception $exception) {

            Log::error("Failed to generate Page plan", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to generate Page plan. Please try again.',
            ];
        }
    }

    public function generateStep15(NovelChapterSummaryRequest $request, Novel $novel): array
    {
        try {
            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $chapterPlan = $novel->chapter_plan ?? [];

            if (! is_array($chapterPlan) || empty($chapterPlan)) {
                throw new Exception('Chapter plan is empty.');
            }

            $context               = $this->chapterSummaryContextFormatter($novel);
            $additionalInformation = $request->input("additional_information", "Auto");

            foreach ($chapterPlan as $chapterPlanEntry) {
                $this->novelChapterService->generateStep15($novel, $context, (array) $chapterPlanEntry, $aiPrompt->prompt, $aiBrain, $additionalInformation);
            }

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

    public function generateStep16(NovelChapterContentRequest $request, Novel $novel): array
    {
        try {
            $chapterNo = $request->input("chapter_no");

            $novelChapter = $novel->novelChapters()
                ->where('no', (string) $chapterNo)
                ->first();

            if (! $novelChapter) {
                throw new Exception('Novel chapter ' . $chapterNo . ' not found.');
            }

            $step = AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16;

            $aiPrompt = $this->aiPromptService->findByCode(Str::studly($step));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $context               = $this->chapterContentContextFormatter($novel);
            $additionalInformation = $request->input("additional_information", "Auto");

            $this->novelChapterService->generateStep16($novel, $novelChapter, $context, $aiPrompt->prompt, $aiBrain, $additionalInformation);

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

    public function delete(Novel $novel): array
    {

        try {

            DB::transaction(function () use ($novel) {
                $novel->delete();
            });

            return [
                'status'  => 'success',
                'message' => 'Novel deleted successfully.',
            ];
        } catch (Exception $exception) {

            Log::error('Novel delete failed.', [
                'exception' => $exception,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to delete novel. Please try again.',
            ];
        }
    }

    private function chapterSummaryContextFormatter(Novel $novel): array
    {
        return [
            "foundation"               => json_encode($novel->foundation, JSON_PRETTY_PRINT),
            "characters"               => json_encode($novel->characters, JSON_PRETTY_PRINT),
            "story_structure"          => json_encode($novel->story_structure, JSON_PRETTY_PRINT),
            "twists_and_foreshadowing" => json_encode($novel->twists_and_foreshadowing, JSON_PRETTY_PRINT),
        ];
    }

    private function chapterContentContextFormatter(Novel $novel): array
    {
        return [
            "language"                 => $novel->language?->name,
            "foundation"               => json_encode($novel->foundation, JSON_PRETTY_PRINT),
            "characters"               => json_encode($novel->characters, JSON_PRETTY_PRINT),
            "world_bible"              => json_encode($novel->world_bible, JSON_PRETTY_PRINT),
            "story_structure"          => json_encode($novel->story_structure, JSON_PRETTY_PRINT),
            "twists_and_foreshadowing" => json_encode($novel->twists_and_foreshadowing, JSON_PRETTY_PRINT),
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
