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
use App\Services\BackOffice\NovelGeneratorService;
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
    protected NovelGeneratorService $novelGeneratorService;

    public function __construct(AiBrainService $aiBrainService, AiPromptService $aiPromptService, AudienceService $audienceService, GenreService $genreService, NovelTypeService $novelTypeService, HuggingFaceApiService $huggingFaceApiService, LanguageService $languageService, NovelChapterService $novelChapterService, NovelGeneratorService $novelGeneratorService)
    {
        $this->aiBrainService        = $aiBrainService;
        $this->aiPromptService       = $aiPromptService;
        $this->audienceService       = $audienceService;
        $this->genreService          = $genreService;
        $this->novelTypeService      = $novelTypeService;
        $this->huggingFaceApiService = $huggingFaceApiService;
        $this->languageService       = $languageService;
        $this->novelChapterService   = $novelChapterService;
        $this->novelGeneratorService   = $novelGeneratorService;
    }

    public function new(): Novel
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

    public function generateStep1(StoryBookStep1 $request, Novel $novel): array
    {
        $isNew       = empty($novel->id);
        $statusEvent = $isNew ? "save" : "update";

        try {

            $stepData = $this->novelGeneratorService->generateStep1($request, $novel);

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

    public function generateStep2(StoryBookStep2 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep2($request, $novel);

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

    public function generateStep3(StoryBookStep3 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep3($request, $novel);

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

    public function generateStep4(StoryBookStep4 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep4($request, $novel);
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

    public function generateStep5(StoryBookStep5 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep5($request, $novel);

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

    public function generateStep6(StoryBookStep6 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep6($request, $novel);

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

    public function generateStep7(StoryBookStep7 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep7($request, $novel);

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

    public function generateStep8(StoryBookStep8 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep8($request, $novel);


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

    public function generateStep9(StoryBookStep9 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep9($request, $novel);

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

    public function generateStep10(StoryBookStep10 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep10($request, $novel);

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

    public function generateStep11(StoryBookStep11 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep11($request, $novel);

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

    public function generateStep12(StoryBookStep12 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep12($request, $novel);

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

    public function generateStep13(StoryBookStep13 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep13($request, $novel);

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

    public function generateStep14(StoryBookStep14 $request, Novel $novel): array
    {
        try {
            $stepData = $this->novelGeneratorService->generateStep14($request, $novel);

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

    public function generateStep15(StoryBookStep15 $request, Novel $novel): array
    {
        return $this->novelGeneratorService->generateStep15($request, $novel);
    }

    public function generateStep16(StoryBookStep16 $request, Novel $novel): array
    {
        return $this->novelGeneratorService->generateStep16($request, $novel);
    }

    public function reviewNovel(Novel $novel): array
    {
        return $this->novelGeneratorService->reviewNovel($novel);
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
}
