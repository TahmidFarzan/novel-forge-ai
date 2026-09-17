<?php
namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Http\Requests\NovelCharactersRequest;
use App\Http\Requests\NovelFoundationRequest;
use App\Models\Novel;
use App\Services\BackOffice\AiBrainService;
use App\Services\BackOffice\AiPromptService;
use App\Services\BackOffice\AudienceService;
use App\Services\BackOffice\GenreService;
use App\Services\BackOffice\HuggingFaceApiService;
use App\Services\BackOffice\LanguageService;
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

    public function __construct(AiBrainService $aiBrainService, AiPromptService $aiPromptService, AudienceService $audienceService, GenreService $genreService, NovelTypeService $novelTypeService, HuggingFaceApiService $huggingFaceApiService, LanguageService $languageService)
    {
        $this->aiBrainService        = $aiBrainService;
        $this->aiPromptService       = $aiPromptService;
        $this->audienceService       = $audienceService;
        $this->genreService          = $genreService;
        $this->novelTypeService      = $novelTypeService;
        $this->huggingFaceApiService = $huggingFaceApiService;
        $this->languageService       = $languageService;
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

    public function generateFoundation(NovelFoundationRequest $request, Novel $novel): array
    {
        $isNew       = empty($novel->id);
        $statusEvent = $isNew ? "save" : "update";

        try {
            $aiPrompt = $this->aiPromptService->findByCode(Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION_GENERATOR));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $receivedInputs = $this->foundationRequestInputsFormatter($request->input("language_id"), $request->input("audience_id"), $request->input("novel_type_id"), $request->input("genre_ids"), $request->input("additional_information", "Auto"));
            $prompt         = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $receivedInputs);

            $apiResponse = $this->huggingFaceApiService->sendPostRequest($aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $prompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            $novel = DB::transaction(function () use ($request, $apiResponse, $novel, $isNew) {
                $foundationObject = $this->extractFoundationFromResponse($apiResponse);

                $novel->title     = $foundationObject->title;
                $novel->sub_title = $foundationObject->subtitle;
                $novel->foundation      = $foundationObject->foundation;

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

    public function generateCharacters(NovelCharactersRequest $request, Novel $novel): array
    {
        try {
            $aiPrompt = $this->aiPromptService->findByCode(Str::studly(AiPromptGeneratorHelper::AI_PROMPT_NAME_CHARACTER_GENERATOR));
            $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));

            $requestInputs = $this->charactersRequestInputsFormatter($novel, $request->input("additional_information", "Auto"));
            $prompt        = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $requestInputs);

            $apiResponse = $this->huggingFaceApiService->sendPostRequest($aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $prompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

            DB::transaction(function () use ($apiResponse, $novel) {
                $characterObject   = $this->extractCharactersFromResponse($apiResponse);
                $novel->characters = $characterObject;
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

    private function extractFoundationFromResponse($apiResponse): object
    {
        $content = data_get(
            $apiResponse,
            'choices.0.message.content'
        );

        if (! is_string($content) || trim($content) === '') {
            throw new Exception('Invalid AI response structure.');
        }

        $content = trim($content);

        $content = preg_replace(
            '/^```(?:json)?\s*|\s*```$/i',
            '',
            $content
        );

        $content = trim($content);

        $decoded = json_decode(
            $content,
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            ! is_array($decoded)
        ) {
            throw new Exception(
                'AI response is not valid JSON: ' . json_last_error_msg()
            );
        }

        return (object) [
            'title'    => $decoded['novel_title'] ?? null,
            'subtitle' => $decoded['novel_subtitle'] ?? null,
            'foundation'     => $decoded['novel_foundation'] ?? null,
        ];
    }

    private function foundationRequestInputsFormatter(int | string $languageId, int | string $audienceId, int | string $novelTypeId, array $genreIds, string $additionalInformation): array
    {
        $receivedInputs = [];

        $language  = $this->languageService->findByIdsOrEnglish($languageId);
        $audience  = $this->audienceService->findById($audienceId);
        $novelType = $this->audienceService->findById($novelTypeId);
        $genres    = $this->genreService->findByIdsOrRandom($genreIds);

        $genrePromptInstruction = '';
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
            "language"                 => $language?->name,
            "additional_information"   => $additionalInformation,
            "genre_prompt_instruction" => $genrePromptInstruction,
            "audience_instruction"     => $audience->prompt_instruction,
            "novel_type_instruction"   => $novelType->prompt_instruction,
        ];

        return $receivedInputs;
    }

    private function charactersRequestInputsFormatter(Novel $novel, string $additionalIinformation): array
    {
        $requestInputs = [];

        $formatedFoundation  = json_encode($novel->foundation, JSON_PRETTY_PRINT);
        $requestInputs = [
            "foundation"                             => $formatedFoundation,
            "additional_information" => $additionalIinformation,
        ];

        return $requestInputs;
    }

    private function extractCharactersFromResponse($apiResponse): object
    {
        $content = data_get(
            $apiResponse,
            'choices.0.message.content'
        );

        if (! is_string($content) || trim($content) === '') {
            throw new Exception('Invalid AI response structure.');
        }

        $content = trim($content);

        $content = preg_replace(
            '/^```(?:json)?\s*|\s*```$/i',
            '',
            $content
        );

        $content = trim($content);

        $decoded = json_decode(
            $content,
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            ! is_array($decoded)
        ) {
            throw new Exception(
                'AI response is not valid JSON: ' . json_last_error_msg()
            );
        }

        return (object) [
            'characters'            => $decoded['characters'] ?? [],
            'relationship_dynamics' => $decoded['relationship_dynamics'] ?? [],
        ];
    }
}
