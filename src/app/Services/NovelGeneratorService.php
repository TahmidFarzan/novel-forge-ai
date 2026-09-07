<?php
namespace App\Services;

use App\Helpers\NovelGeneratorHelper;
use App\Helpers\UserHelper;
use App\Http\Requests\NovelGeneratorStep1Request;
use App\Models\NovelGenerator;
use App\Services\AiBrainService;
use App\Services\AiPromptService;
use App\Services\GenreService;
use App\Services\OpenAiApiService;
use App\Services\NovelGeneratorStepService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelGeneratorService
{
    protected AiBrainService $aiBrainService;
    protected AiPromptService $aiPromptService;
    protected GenreService $genreService;
    protected OpenAiApiService $openAiApiService;
    protected NovelGeneratorStepService $novelGeneratorStepService;

    public function __construct(AiBrainService $aiBrainService, AiPromptService $aiPromptService, GenreService $genreService, OpenAiApiService $openAiApiService, NovelGeneratorStepService $novelGeneratorStepService)
    {
        $this->aiBrainService   = $aiBrainService;
        $this->aiPromptService  = $aiPromptService;
        $this->genreService     = $genreService;
        $this->openAiApiService = $openAiApiService;
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
        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

    public function step1Save(NovelGeneratorStep1Request $request, NovelGenerator $novelGenerator): array
    {
        $isNew       = empty($novelGenerator->id);
        $statusEvent = $isNew ? "save" : "update";

        try {

            DB::transaction(function () use ($request, $novelGenerator, $isNew) {

                $novelGenerator->name = "Novel Generator " . now()->format('YmdHis');

                if ($isNew) {
                    $novelGenerator->created_by_id = Auth::id();
                }

                $novelGenerator->status = NovelGeneratorHelper::STATUS_ONGOING;

                if ($novelGenerator->save()) {

                    $genreNames        = [];
                    $genreInstructions = [];

                    $mainCharacterGender = $request->input("main_character_gender",UserHelper::USER_GENDER_MALE);
                    $is18Plus = $request->boolean("is_18_plus",false);
                    $enableMatureContent = $request->boolean("enable_mature_content",false);
                    $language = $request->input("language","English");
                    $additionalInformation = $request->input("additional_information", "Auto");
                    $novelContinuity = $request->input("novel_continuity",NovelGeneratorHelper::CONTINUITY_STANDALONE);

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

                    $aiBrain = $this->aiBrainService->findById($request->input("ai_brain_id"));
                    $aiPrompt = $this->aiPromptService->findByStepNumber(1);

                    $prompt = str_replace(
                        [
                            '{{genres}}',
                            '{{main_character_gender}}',
                            '{{is_18_plus}}',
                            '{{enable_mature_content}}',
                            '{{language}}',
                            '{{novel_continuity}}',
                            '{{additional_information}}',
                            '{{genre_instructions}}',
                        ],
                        [
                            $genreNames,
                            $mainCharacterGender,
                            $is18Plus ? "True" : "False",
                            $enableMatureContent ? "True" : "False",
                            $language,
                            $novelContinuity,
                            $additionalInformation ?? "Auto",
                            $genrePromptInstruction,
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
}
