<?php

namespace App\Services\BackOffice;

use App\Exceptions\AiResponseException;
use App\Helpers\AiPromptGeneratorHelper;
use Exception;
use App\Models\Novel;
use App\Models\NovelChapter;
use App\Support\PromptContextEncoder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceApiService
{
    protected int $defaultTimeout = 120;

    protected AiResponseService $aiResponseService;

    public function __construct(AiResponseService $aiResponseService)
    {
        $this->aiResponseService = $aiResponseService;
    }

    public function sendPostRequest(string $stepName, string $url, string $apiKey, string $model, mixed $data = null, ?int $maxOutputTokens = null, ?int $timeout = null): array
    {
        $requestTimeout = $timeout ?? $this->defaultTimeout;
        $maxAttempts    = max(1, (int) config('services.ai_generation.max_attempts', 3));
        $retryDelayMs   = max(0, (int) config('services.ai_generation.retry_delay_ms', 750));

        set_time_limit(($requestTimeout * $maxAttempts) + 5);

        $payload  = $this->buildPayload($model, $data, $maxOutputTokens);
        $endpoint = rtrim($url, '/');

        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $response = $this->post($stepName, $endpoint, $apiKey, $payload, $requestTimeout);

                return $this->aiResponseFormats($stepName, $response);
            } catch (AiResponseException $exception) {
                if (! $exception->isRetryable() || $attempt >= $maxAttempts) {
                    throw $exception->withContext([
                        'attempts'     => $attempt,
                        'max_attempts' => $maxAttempts,
                    ]);
                }

                Log::warning('Retrying AI generation step after an unusable AI response.', [
                    'step'         => $stepName,
                    'attempt'      => $attempt,
                    'max_attempts' => $maxAttempts,
                    'reason'       => $exception->context()['reason'] ?? null,
                ]);

                if ($retryDelayMs > 0) {
                    usleep($retryDelayMs * 1000 * $attempt);
                }
            }
        }
    }

    public function sendGetRequest(string $url, string $apiKey, array $params = [], ?int $timeout = null): array
    {
        $response = Http::timeout(
            $timeout ?? $this->defaultTimeout
        )
            ->withToken($apiKey)
            ->acceptJson()
            ->get(
                rtrim($url, '/'),
                $params
            );

        if (! $response->successful()) {
            throw new Exception(
                $response->body()
            );
        }

        return $response->json();
    }


    public function aiResponseFormats(string $stepName, mixed $apiResponse): array
    {
        $content = $this->aiResponseService->contentFromApiResponse($stepName, $apiResponse);

        if ($stepName === AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16) {
            return $this->apiStep16ResponseFormat($this->extractStep16ChapterContentFromResponse($stepName, $content));
        }

        $decoded = $this->aiResponseService->decodeAndValidate($content, $stepName);

        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1     => $this->apiStep1ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2     => $this->apiStep2ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3     => $this->apiStep3ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4     => $this->apiStep4ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5     => $this->apiStep5ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6     => $this->apiStep6ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7     => $this->apiStep7ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8     => $this->apiStep8ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9     => $this->apiStep9ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10    => $this->apiStep10ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11    => $this->apiStep11ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12    => $this->apiStep12ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13    => $this->apiStep13ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14    => $this->apiStep14ResponseFormat($decoded),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15    => $this->apiStep15ResponseFormat($decoded),
            default                                                => throw new Exception("Unknown AI step name [{$stepName}]."),
        };
    }

    public function step1InputsFormatter(array $inputs): array
    {
        $language               = $inputs['language'] ?? null;
        $audience               = $inputs['audience'] ?? null;
        $novelType              = $inputs['novel_type'] ?? null;
        $genres                 = collect($inputs['genres'] ?? []);
        $genrePromptInstruction = '';

        foreach ($genres as $genre) {

            $gInstruction = trim($genre->prompt_instruction ?? '');

            if ($gInstruction === '') {
                continue;
            }

            if (! str_ends_with($gInstruction, '.')) {
                $gInstruction .= '.';
            }

            if ($genrePromptInstruction !== '') {
                $genrePromptInstruction .= ' ';
            }

            $genrePromptInstruction .= $gInstruction;
        }

        return [
            "language"                 => $language?->name,
            "additional_information"   => $inputs['additional_information'] ?? 'Auto',
            "genre_prompt_instruction" => $genrePromptInstruction,
            "audience_instruction"     => $audience?->prompt_instruction,
            "novel_type_instruction"   => $novelType?->prompt_instruction,
        ];
    }

    public function step2InputsFormatter(Novel $novel): array
    {
        return [
            "foundation"             => PromptContextEncoder::encode($novel->foundation ?? []),
        ];
    }

    public function step3InputsFormatter(Novel $novel): array
    {
        return [
            "foundation"             => PromptContextEncoder::encode($novel->foundation ?? []),
            "characters"             => PromptContextEncoder::encode($novel->characters ?? []),
        ];
    }

    public function step4InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
            "characters"             => PromptContextEncoder::encode($novel->characters ?? []),
        ];
    }

    public function step5InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
            "locations"              => PromptContextEncoder::encode($novel->locations ?? []),
        ];
    }

    public function step6InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
            "locations"              => PromptContextEncoder::encode($novel->locations ?? []),
            "factions"               => PromptContextEncoder::encode($novel->factions ?? []),
        ];
    }

    public function step7InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
            "creatures"              => PromptContextEncoder::encode($novel->creatures ?? []),
            "factions"               => PromptContextEncoder::encode($novel->factions ?? []),
        ];
    }

    public function step8InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
            "factions"               => PromptContextEncoder::encode($novel->factions ?? []),
            "foundation"             => PromptContextEncoder::encode($novel->foundation ?? []),
        ];
    }

    public function step9InputsFormatter(Novel $novel): array
    {
        return [
            "foundation"             => PromptContextEncoder::encode($novel->foundation ?? []),
            "characters"             => PromptContextEncoder::encode($novel->characters ?? []),
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
            "timeline"               => PromptContextEncoder::encode($novel->timeline ?? []),
        ];
    }

    public function step10InputsFormatter(Novel $novel): array
    {
        return [
            "story_structure"        => PromptContextEncoder::encode($novel->story_structure ?? []),
            "characters"             => PromptContextEncoder::encode($novel->characters ?? []),
            "world_bible"            => PromptContextEncoder::encode($novel->world_bible ?? []),
        ];
    }

    public function step11InputsFormatter(Novel $novel): array
    {
        return [
            "story_structure"          => PromptContextEncoder::encode($novel->story_structure ?? []),
            "twists_and_foreshadowing" => PromptContextEncoder::encode($novel->twists_and_foreshadowing ?? []),
            "locations"                => PromptContextEncoder::encode($novel->locations ?? []),
        ];
    }

    public function step12InputsFormatter(Novel $novel): array
    {
        return [
            "characters"             => PromptContextEncoder::encode($novel->characters ?? []),
            "scene_plans"            => PromptContextEncoder::encode($novel->scene_plans ?? []),
        ];
    }

    public function step13InputsFormatter(Novel $novel): array
    {
        return [
            "scene_plans"            => PromptContextEncoder::encode($novel->scene_plans ?? []),
            "story_structure"        => PromptContextEncoder::encode($novel->story_structure ?? []),
        ];
    }

    public function step14InputsFormatter(Novel $novel): array
    {
        return [
            "chapter_plan"           => PromptContextEncoder::encode($novel->chapter_plan ?? []),
            "scene_plans"            => PromptContextEncoder::encode($novel->scene_plans ?? []),
        ];
    }

    public function step15InputsFormatter(Novel $novel, $chapterPlanEntry): array
    {
        $scenePlans = $this->chapterScenePlans($novel, $chapterPlanEntry);

        return [
            "foundation"               => PromptContextEncoder::encode($novel->foundation ?? []),
            "characters"               => PromptContextEncoder::encode($novel->characters ?? []),
            "story_structure"          => PromptContextEncoder::encode($novel->story_structure ?? []),
            "twists_and_foreshadowing" => PromptContextEncoder::encode($novel->twists_and_foreshadowing ?? []),
            "chapter_plan_entry"       => PromptContextEncoder::encode($chapterPlanEntry ?? []),
            "scene_plans"              => PromptContextEncoder::encode($scenePlans ?? []),
        ];
    }

    public function step16InputsFormatter(Novel $novel, NovelChapter $novelChapter): array
    {

        $chapterPlanEntry = $this->findChapterPlanEntry($novel, $novelChapter);
        $scenePlans       = $this->chapterScenePlans($novel, $chapterPlanEntry);
        $dialoguePlans    = $this->chapterDialoguePlans($novel, $scenePlans);

        return [
            "language"                 => $novel->language?->name ?? '',
            "foundation"               => PromptContextEncoder::encode($novel->foundation ?? []),
            "characters"               => PromptContextEncoder::encode($novel->characters ?? []),
            "world_bible"              => PromptContextEncoder::encode($novel->world_bible ?? []),
            "story_structure"          => PromptContextEncoder::encode($novel->story_structure ?? []),
            "twists_and_foreshadowing" => PromptContextEncoder::encode($novel->twists_and_foreshadowing ?? []),
            "chapter_summary"          => $novel->chapter_summary ?? '',
            "chapter_plan_entry"       => PromptContextEncoder::encode($chapterPlanEntry ?? []),
            "scene_plans"              => PromptContextEncoder::encode($scenePlans ?? []),
            "dialogue_plans"           => PromptContextEncoder::encode($dialoguePlans ?? []),
        ];
    }

    private function post(string $stepName, string $endpoint, string $apiKey, array $payload, int $timeout): mixed
    {
        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($endpoint, $payload);
        } catch (\Throwable $exception) {
            throw AiResponseException::transport(
                $stepName,
                $this->safeMessage($exception),
                true,
                ['exception' => $exception::class],
            );
        }

        if (! $response->successful()) {
            throw AiResponseException::transport(
                $stepName,
                sprintf('the provider responded with HTTP %d.', $response->status()),
                $this->isRetryableStatus($response->status()),
                [
                    'status'        => $response->status(),
                    'body_excerpt'  => mb_substr((string) $response->body(), 0, 300),
                ],
            );
        }

        return $response->json();
    }

    private function isRetryableStatus(int $status): bool
    {
        return $status === 408 || $status === 429 || $status >= 500;
    }

    private function safeMessage(\Throwable $exception): string
    {
        return mb_substr(preg_replace('/\s+/', ' ', $exception->getMessage()) ?: 'connection error', 0, 200);
    }

    private function buildPayload(string $model, mixed $data = null, ?int $maxOutputTokens = null): array
    {
        $content = $this->buildContent($data);

        $payload = [
            'model'    => $model,
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => $content,
                ],
            ],
        ];

        if ($maxOutputTokens !== null && $maxOutputTokens > 0) {
            $payload['max_tokens'] = $maxOutputTokens;
        }

        if (config('services.ai_generation.json_response_format')) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $payload;
    }

    private function buildContent(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        if (is_string($data)) {
            return $data;
        }

        if (is_array($data)) {
            if (isset($data['content'])) {
                return (string) $data['content'];
            }

            return json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return (string) $data;
    }

    private function extractStep16ChapterContentFromResponse(string $stepName, string $content): array
    {
        $normalized = $this->aiResponseService->normalize($content);

        $decoded = json_decode($normalized, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $candidate = $decoded['chapter_content'] ?? null;

            if (! is_string($candidate) || trim($candidate) === '') {
                throw AiResponseException::structureMismatch(
                    $stepName,
                    'a JSON object was returned but the "chapter_content" field was missing or empty.',
                    $this->aiResponseService->diagnostics($normalized, $stepName),
                );
            }

            return [
                'chapter_content' => trim($candidate),
            ];
        }

        if (trim($normalized) === '') {
            throw AiResponseException::transport($stepName, 'the provider returned empty chapter content.', false);
        }

        return [
            'chapter_content' => trim($normalized),
        ];
    }

    private function apiStep1ResponseFormat(array $decoded): array
    {
        return [
            'title'      => $decoded['novel_title'],
            'subtitle'   => $decoded['novel_subtitle'],
            'foundation' => $decoded['novel_foundation'],
        ];
    }

    private function apiStep2ResponseFormat(array $decoded): array
    {
        return [
            'characters' => [
                'characters'    => $this->formatAsList($decoded['characters'] ?? []),
                'relationships' => $this->formatAsList($decoded['relationships'] ?? []),
            ],
        ];
    }

    private function apiStep3ResponseFormat(array $decoded): array
    {
        return [
            'world_bible' => $this->formatAsObject($decoded['world_bible'] ?? []),
        ];
    }

    private function apiStep4ResponseFormat(array $decoded): array
    {
        return [
            'locations' => $this->formatAsObject($decoded['locations'] ?? []),
        ];
    }

    private function apiStep5ResponseFormat(array $decoded): array
    {
        return [
            'factions' => $this->formatAsObject($decoded['factions'] ?? []),
        ];
    }

    private function apiStep6ResponseFormat(array $decoded): array
    {
        return [
            'creatures' => $this->formatAsObject($decoded['creatures'] ?? []),
        ];
    }

    private function apiStep7ResponseFormat(array $decoded): array
    {
        return [
            'systems' => $this->formatAsObject($decoded['systems'] ?? []),
        ];
    }

    private function apiStep8ResponseFormat(array $decoded): array
    {
        return [
            'timeline' => $this->formatAsObject($decoded['timeline'] ?? []),
        ];
    }

    private function apiStep9ResponseFormat(array $decoded): array
    {
        return [
            'story_structure' => $this->formatAsObject($decoded['story_structure'] ?? []),
        ];
    }

    private function apiStep10ResponseFormat(array $decoded): array
    {
        return [
            'twists_and_foreshadowing' => $this->formatAsObject($decoded['twists_and_foreshadowing'] ?? []),
        ];
    }

    private function apiStep11ResponseFormat(array $decoded): array
    {
        return [
            'scene_plans' => $this->formatAsList($decoded['scene_plans'] ?? []),
        ];
    }

    private function apiStep12ResponseFormat(array $decoded): array
    {
        return [
            'dialogue_plans' => $this->formatAsList($decoded['dialogue_plans'] ?? []),
        ];
    }

    private function apiStep13ResponseFormat(array $decoded): array
    {
        return [
            'chapter_plan' => $this->formatAsList($decoded['chapter_plan'] ?? []),
        ];
    }

    private function apiStep14ResponseFormat(array $decoded): array
    {
        return [
            'page_plan' => $this->formatAsList($decoded['page_plan'] ?? []),
        ];
    }

    private function apiStep15ResponseFormat(array $decoded): array
    {
        $chapterSummary = $decoded['chapter_summary'] ?? null;

        if (! is_array($chapterSummary) || $chapterSummary === []) {
            throw AiResponseException::structureMismatch(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
                'the "chapter_summary" object was missing or empty.',
            );
        }

        $encoded = json_encode($chapterSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($encoded)) {
            throw AiResponseException::structureMismatch(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
                'the "chapter_summary" object could not be re-encoded for storage.',
            );
        }

        return [
            'chapter_summary' => $encoded,
        ];
    }

    private function apiStep16ResponseFormat(array $extracted): array
    {
        return [
            'chapter_content' => (string) ($extracted['chapter_content'] ?? ''),
        ];
    }

    private function formatAsList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_is_list($value) ? $value : [$value];
    }

    private function formatAsObject(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private function findChapterPlanEntry(Novel $novel, NovelChapter $novelChapter): array
    {
        $chapterPlan = $novel->chapter_plan ?? [];

        if (is_array($chapterPlan)) {
            foreach ($chapterPlan as $entry) {
                if ((string) ($entry['chapter_number'] ?? '') === (string) $novelChapter->no) {
                    return (array) $entry;
                }
            }
        }

        return [
            'chapter_number' => $novelChapter->no,
            'title'          => $novelChapter->title,
            'summary'        => $novelChapter->summery,
            'scenes'         => [],
            'chapter_goals'  => [],
            'pacing_and_flow' => '',
        ];
    }

    private function chapterDialoguePlans(Novel $novel, array $scenePlans): array
    {
        $dialoguePlans = $novel->dialogue_plans ?? [];

        if (! is_array($dialoguePlans) || empty($dialoguePlans)) {
            return [];
        }

        $sceneNumbers = array_values(array_filter(array_map(function ($scene) {
            return (string) ($scene['scene_number'] ?? '');
        }, $scenePlans)));

        if (empty($sceneNumbers)) {
            return [];
        }

        return array_values(array_filter($dialoguePlans, function ($dialogue) use ($sceneNumbers) {
            return isset($dialogue['scene_reference']) && in_array((string) $dialogue['scene_reference'], $sceneNumbers, true);
        }));
    }

    private function chapterScenePlans(Novel $novel, array $chapterPlanEntry): array
    {
        $scenePlans = $novel->scene_plans ?? [];

        if (! is_array($scenePlans) || empty($scenePlans)) {
            return [];
        }

        $chapterNumber = (string) ($chapterPlanEntry['chapter_number'] ?? '');

        return array_values(array_filter($scenePlans, function ($scene) use ($chapterNumber) {
            return isset($scene['chapter']) && (string) $scene['chapter'] === $chapterNumber;
        }));
    }
}
