<?php

namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Http\Requests\NovelPlotRequest;
use App\Models\Novel;
use App\Services\BackOffice\AiBrainService;
use App\Services\BackOffice\AiPromptService;
use App\Services\BackOffice\AudienceService;
use App\Services\BackOffice\GenreService;
use App\Services\BackOffice\LanguageService;
use App\Services\BackOffice\NovelTypeService;
use App\Services\BackOffice\HuggingFaceApiService;
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

    public function __construct(AiBrainService $aiBrainService, AiPromptService $aiPromptService, AudienceService $audienceService, GenreService $genreService, NovelTypeService $novelTypeService, HuggingFaceApiService $huggingFaceApiService, LanguageService $languageService)
    {
        $this->aiBrainService   = $aiBrainService;
        $this->aiPromptService  = $aiPromptService;
        $this->audienceService  = $audienceService;
        $this->genreService     = $genreService;
        $this->novelTypeService = $novelTypeService;
        $this->huggingFaceApiService = $huggingFaceApiService;
        $this->languageService  = $languageService;
    }

    public function new(): Novel
    {
        return new Novel();
    }

    public function find(string $slug): Novel
    {
        return Novel::with([
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

    public function savePlot(NovelPlotRequest $request, Novel $novel): array
    {
        $isNew       = empty($novel->id);
        $statusEvent = $isNew ? "save" : "update";

        try {
            $aiPrompt = $this->aiPromptService->findByCode(Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLOT_GENERATOR));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $language  = $this->languageService->findByIdsOrEnglish($request->input("language_id"));
            $audience  = $this->audienceService->findById($request->input("audience_id"));
            $novelType = $this->audienceService->findById($request->input("novel_type_id"));
            $genres    = $this->genreService->findByIdsOrRandom($request->input("genre_ids"));

            $genrePromptInstruction = '';
            $is18Plus               = $request->boolean("is_18_plus", false) ? "True" : "False";
            $enableMatureContent    = $request->boolean("enable_mature_content", false) ? "True" : "False";

            $additionalInformation = $request->input("additional_information", "Auto");
            $novelContinuity       = $request->input("novel_continuity", NovelHelper::CONTINUITY_STANDALONE);

            foreach ($genres as $genre) {

                $gInstruction = trim($genre->prompt_instruction);

                if (! str_ends_with($gInstruction, '.')) {
                    $gInstruction .= '.';
                }

                if ($genrePromptInstruction !== '') {
                    $genrePromptInstruction .= ' ';
                }

                $genrePromptInstruction .= $gInstruction;
            }

            $receivedInputs = [
                "is_18_plus" => $is18Plus,
                "enable_mature_content" => $enableMatureContent,
                "language" => $language?->name,
                "novel_continuity" => $novelContinuity,
                "additional_information" => $additionalInformation,
                "genre_prompt_instruction" => $genrePromptInstruction,
                "audience_instruction" => $audience->prompt_instruction,
                "novel_type_instruction" => $novelType->prompt_instruction,
            ];

            $prompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $receivedInputs);

            $apiResponse = $this->huggingFaceApiService->sendPostRequest($aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $prompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            Log::info("Novel AI Response", ["apiResponse" => $apiResponse]);

            $novel = DB::transaction(function () use ($request, $apiResponse, $receivedInputs, $novel, $isNew) {
                $novelObject = $this->extractNovelFromResponse($apiResponse);

                $novel->title     = $novelObject->title;
                $novel->sub_title = $novelObject->subtitle;
                $novel->plot      = $novelObject->plot;

                $novel->received_inputs      = $receivedInputs;

                $novel->audience_id   = $request->input("audience_id");
                $novel->novel_type_id = $request->input("novel_type_id");
                $novel->language_id   = $request->input("language_id");
                $novel->status        = NovelHelper::STATUS_ONGOING;

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
                "novel" => $novel,
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
                'status'  => 'error',
                'message' => 'Failed to save novel. Please try again.',
            ];
        }
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

    private function extractNovelFromResponse($apiResponse): object
    {
        $content = data_get(
            $apiResponse,
            'choices.0.message.content'
        );

        if (! $content) {
            throw new Exception("Invalid AI response structure.");
        }

        $content = trim($content);

        $content = str_replace(
            [
                '```json',
                '```',
            ],
            '',
            $content
        );

        $decoded = json_decode(
            trim($content),
            true
        );

        if (! is_array($decoded)) {
            throw new Exception("AI response is not valid JSON.");
        }

        return (object) [
            'title'    => $decoded['novel_title'] ?? null,
            'subtitle' => $decoded['novel_subtitle'] ?? null,
            'plot'     => $decoded['novel_plot'] ?? null,
        ];
    }
}
