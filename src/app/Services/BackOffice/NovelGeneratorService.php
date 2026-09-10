<?php
namespace App\Services\BackOffice;

use App\Helpers\NovelGeneratorHelper;
use App\Helpers\UserHelper;
use App\Http\Requests\NovelGeneratorStep1Request;
use App\Models\NovelGenerator;
use App\Services\BackOffice\AiBrainService;
use App\Services\BackOffice\AiPromptService;
use App\Services\BackOffice\AudienceService;
use App\Services\BackOffice\GenreService;
use App\Services\BackOffice\NovelTypeService;
use App\Services\BackOffice\NovelGeneratorStepService;
use App\Services\BackOffice\OpenAiApiService;
use Exception;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelGeneratorService
{
    protected AiBrainService $aiBrainService;
    protected AiPromptService $aiPromptService;
    protected AudienceService $audienceService;
    protected GenreService $genreService;
    protected NovelTypeService $novelTypeService;
    protected OpenAiApiService $openAiApiService;
    protected NovelGeneratorStepService $novelGeneratorStepService;

    public function __construct(AiBrainService $aiBrainService, AiPromptService $aiPromptService, AudienceService $audienceService, GenreService $genreService, NovelTypeService $novelTypeService, OpenAiApiService $openAiApiService, NovelGeneratorStepService $novelGeneratorStepService)
    {
        $this->aiBrainService            = $aiBrainService;
        $this->aiPromptService           = $aiPromptService;
        $this->audienceService           = $audienceService;
        $this->genreService              = $genreService;
        $this->novelTypeService          = $novelTypeService;
        $this->openAiApiService          = $openAiApiService;
        $this->novelGeneratorStepService = $novelGeneratorStepService;
    }

    public function new (): NovelGenerator
    {
        return new NovelGenerator();
    }

    public function find(string $slug): NovelGenerator
    {
        return NovelGenerator::with([
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

        $query = NovelGenerator::query();

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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        return $query->with(['novelGeneratorSteps' => fn($q) => $q->orderBy('id')])
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

    public function step1Save(NovelGeneratorStep1Request $request, NovelGenerator $novelGenerator): array
    {
        $isNew       = empty($novelGenerator->id);
        $statusEvent = $isNew ? "save" : "update";

        try {

            $novelGenerator = DB::transaction(function () use ($request, $novelGenerator, $isNew) {

                $novelGenerator->name = "Novel Generator " . now()->format('YmdHis');

                if ($isNew) {
                    $novelGenerator->created_by_id = Auth::id();
                }

                $novelGenerator->status = NovelGeneratorHelper::STATUS_ONGOING;

                if ($novelGenerator->save()) {
                    $language = Language::where("id",$request->input("language_id"))->firstOrFail();

                    $genreNames        = [];
                    $genreInstructions = [];

                    $audienceNames        = [];
                    $audienceInstructions = [];

                    $novelTypeNames        = [];
                    $novelTypeInstructions = [];

                    $mainCharacterGender   = $request->input("main_character_gender", UserHelper::USER_GENDER_MALE);
                    $is18Plus              = $request->boolean("is_18_plus", false);
                    $enableMatureContent   = $request->boolean("enable_mature_content", false);
                    $language              = $language?->name ?? "English";
                    $additionalInformation = $request->input("additional_information", "Auto");
                    $novelContinuity       = $request->input("novel_continuity", NovelGeneratorHelper::CONTINUITY_STANDALONE);

                    $genres = $this->genreService->findByIdsOrRandom($request->input("genre_ids"));
                    foreach ($genres as $genre) {

                        $genreNames[] = $genre->name;

                        if (! empty($genre->prompt_instruction)) {

                            $instruction = trim($genre->prompt_instruction);

                            if (! empty($instruction)) {
                                $genreInstructions[] = $instruction;
                            }
                        }
                    }

                    $genreNames = implode(", ", $genreNames);

                    $genrePromptInstruction = '';

                    foreach ($genreInstructions as $instruction) {

                        $instruction = trim($instruction);

                        if (! str_ends_with($instruction, '.')) {
                            $instruction .= '.';
                        }

                        if ($genrePromptInstruction !== '') {
                            $genrePromptInstruction .= ' ';
                        }

                        $genrePromptInstruction .= $instruction;
                    }

                    $audiences = $this->audienceService->findByIdsOrRandom($request->input("audience_ids"));
                    foreach ($audiences as $audience) {

                        $audienceNames[] = $audience->name;

                        if (! empty($audience->prompt_instruction)) {

                            $instruction = trim($audience->prompt_instruction);

                            if (! empty($instruction)) {
                                $audienceInstructions[] = $instruction;
                            }
                        }
                    }

                    $audienceNames = implode(", ", $audienceNames);

                    $audiencePromptInstruction = '';

                    foreach ($audienceInstructions as $instruction) {

                        $instruction = trim($instruction);

                        if (! str_ends_with($instruction, '.')) {
                            $instruction .= '.';
                        }

                        if ($audiencePromptInstruction !== '') {
                            $audiencePromptInstruction .= ' ';
                        }

                        $audiencePromptInstruction .= $instruction;
                    }

                    $novelTypes = $this->novelTypeService->findByIdsOrRandom($request->input("novel_type_ids"));
                    foreach ($novelTypes as $novelType) {

                        $novelTypeNames[] = $novelType->name;

                        if (! empty($novelType->prompt_instruction)) {

                            $instruction = trim($novelType->prompt_instruction);

                            if (! empty($instruction)) {
                                $novelTypeInstructions[] = $instruction;
                            }
                        }
                    }

                    $novelTypeNames = implode(", ", $novelTypeNames);

                    $novelTypePromptInstruction = '';

                    foreach ($novelTypeInstructions as $instruction) {

                        $instruction = trim($instruction);

                        if (! str_ends_with($instruction, '.')) {
                            $instruction .= '.';
                        }

                        if ($novelTypePromptInstruction !== '') {
                            $novelTypePromptInstruction .= ' ';
                        }

                        $novelTypePromptInstruction .= $instruction;
                    }

                    $aiBrain  = $this->aiBrainService->findById($request->input("ai_brain_id"));
                    $aiPrompt = $this->aiPromptService->findByStepNumber(1);

                    $prompt = str_replace(
                        [
                            '{{genres}}',
                            '{{audiences}}',
                            '{{novel_types}}',
                            '{{main_character_gender}}',
                            '{{is_18_plus}}',
                            '{{enable_mature_content}}',
                            '{{language}}',
                            '{{novel_continuity}}',
                            '{{additional_information}}',
                            '{{genre_instructions}}',
                            '{{audience_instructions}}',
                            '{{novel_type_instructions}}',
                        ],
                        [
                            $genreNames,
                            $audienceNames,
                            $novelTypeNames,
                            $mainCharacterGender,
                            $is18Plus ? "True" : "False",
                            $enableMatureContent ? "True" : "False",
                            $language,
                            $novelContinuity,
                            $additionalInformation ?? "Auto",
                            $genrePromptInstruction,
                            $audiencePromptInstruction,
                            $novelTypePromptInstruction,
                        ],
                        $aiPrompt->prompt
                    );

                    $response = $this->openAiApiService->sendPostRequest($aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $prompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

                    Log::info("Novel Generator AI Response", [
                        "response" => $response,
                    ]);

                    $this->novelGeneratorStepService->saveUsingNovelGenerator($novelGenerator, $aiPrompt, null, $prompt, $response);
                    Log::info("Novel Generator AI Response", [
                        "response" => $response,
                    ]);
                }

                return $novelGenerator;

            });

            return [
                'status'  => 'success',
                'message' => $isNew
                    ? 'Novel Generator created successfully.'
                    : 'Novel Generator updated successfully.',
            ];

        } catch (Exception $exception) {

            Log::error("Failed to {$statusEvent} novel generator.", [
                "exception" => $exception->getMessage(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to save novel generator. Please try again.',
            ];
        }
    }

    public function delete(NovelGenerator $novelGenerator): array
    {

        try {

            DB::transaction(function () use ($novelGenerator) {
                $novelGenerator->delete();
            });

            return [
                'status'  => 'success',
                'message' => 'Novel Generator deleted successfully.',
            ];
        } catch (Exception $exception) {

            Log::error('Novel Generator delete failed.', [
                'exception' => $exception,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to delete novel generator. Please try again.',
            ];
        }
    }
}
