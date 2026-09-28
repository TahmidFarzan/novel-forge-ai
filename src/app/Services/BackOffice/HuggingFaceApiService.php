<?php

namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Models\Novel;
use App\Models\NovelChapter;
use App\Support\PromptContextEncoder;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceApiService
{
    protected int $defaultTimeout = 120;
    protected int $minTimeout = 300;
    protected int $maxTimeout = 300;

    protected const PREVIEW_LENGTH = 200;

    protected const MAX_FENCE_UNWRAP_DEPTH = 2;

    public function generate(string $stepName, string $url, ?string $apiKey, string $model, string $partialPrompt, array $inputs, ?int $maxOutputTokens = null, ?int $timeout = null): array
    {
        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new Exception(
                sprintf('The AI Brain used for "%s" has no API key configured.', $stepName)
            );
        }

        $fullPrompt = AiPromptGeneratorHelper::applyMaxOutputTokenInstruction(
            AiPromptGeneratorHelper::generateFullPrompt($partialPrompt, $inputs),
            $maxOutputTokens,
        );

        $unresolved = AiPromptGeneratorHelper::unresolvedPlaceholders($fullPrompt);

        if ($unresolved !== []) {
            throw new Exception(
                sprintf('The [%s] prompt still contains unresolved placeholders: %s.', $stepName, implode(', ', $unresolved))
            );
        }

        return $this->sendPostRequest($stepName, $url, $apiKey, $model, $fullPrompt, $maxOutputTokens, $timeout);
    }

    public function sendPostRequest(string $stepName, string $url, string $apiKey, string $model, mixed $data = null, ?int $maxOutputTokens = null, ?int $timeout = null): array
    {
        $requestTimeout = $this->resolveTimeout($timeout);

        set_time_limit($requestTimeout + 30);

        $payload = $this->buildPayload($model, $data, $maxOutputTokens);

        try {
            $response = $this->post($stepName, rtrim($url, '/'), $apiKey, $payload, $requestTimeout);
        } catch (Exception $exception) {
            Log::error('AI generation request failed.', [
                'step'      => $stepName,
                'model'     => $model,
                'timeout'   => $requestTimeout,
                'exception' => $exception::class,
                'reason'    => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $this->aiResponseFormats($stepName, $response);
    }

    public function resolveTimeout(?int $timeout): int
    {
        $minimum = $this->minTimeout;
        $maximum = $this->maxTimeout;

        $requested = $timeout ?? $this->defaultTimeout;

        return min($maximum, max($minimum, $requested));
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

    public function foundationInputs(array $inputs): array
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

    public function planChapterInputs(Novel $novel): array
    {
        return [
            "foundation" => PromptContextEncoder::encode($novel->foundation ?? []),
            "characters" => PromptContextEncoder::encode($novel->characters ?? []),
            "world_bible" => PromptContextEncoder::encode($novel->world_bible ?? []),
            "locations" => PromptContextEncoder::encode($novel->locations ?? []),
            "factions" => PromptContextEncoder::encode($novel->factions ?? []),
            "creatures" => PromptContextEncoder::encode($novel->creatures ?? []),
            "systems" => PromptContextEncoder::encode($novel->systems ?? []),
            "timeline" => PromptContextEncoder::encode($novel->timeline ?? []),
        ];
    }

    public function chapterContentInputs(Novel $novel, NovelChapter $novelChapter): array
    {
        $chapterPlanEntry = $this->findChapterPlanEntry($novel, $novelChapter);
        $scenePlans       = $this->chapterScenePlans($novel, $chapterPlanEntry);
        $dialoguePlans    = $this->chapterDialoguePlans($novel, $scenePlans);

        return [
            "language" => $novel->language?->name ?? '',
            "foundation" => PromptContextEncoder::encode($novel->foundation ?? []),
            "characters" => PromptContextEncoder::encode($novel->characters ?? []),
            "world_bible" => PromptContextEncoder::encode($novel->world_bible ?? []),
            "story_structure" => PromptContextEncoder::encode($novel->story_structure ?? []),
            "twists_and_foreshadowing" => PromptContextEncoder::encode($novel->twists_and_foreshadowing ?? []),
            "chapter_summary" => (string) ($novelChapter->summery ?? ''),
            "chapter_plan_entry" => PromptContextEncoder::encode($chapterPlanEntry ?? []),
            "scene_plans" => PromptContextEncoder::encode($scenePlans ?? []),
            "dialogue_plans" => PromptContextEncoder::encode($dialoguePlans ?? []),
        ];
    }

    public function aiResponseFormats(string $stepName, mixed $apiResponse): array
    {
        $content = $this->messageContent($stepName, $apiResponse);
        $decoded = $this->decodeJsonObject($content, $stepName);

        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION       => $this->foundationResponseFormat($decoded, $stepName),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER     => $this->planChapterResponseFormat($decoded, $stepName),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT  => $this->chapterContentResponseFormat($decoded, $stepName),
            default                                                 => throw new Exception("Unknown AI step name [{$stepName}]."),
        };
    }

    private function messageContent(string $stepName, mixed $apiResponse): string
    {
        if (! is_array($apiResponse)) {
            throw new Exception('The AI response was not usable for this step. The provider response was not a JSON object.');
        }

        $content = data_get($apiResponse, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new Exception('The AI response was not usable for this step. The provider response contained no message content.');
        }

        $finishReason = data_get($apiResponse, 'choices.0.finish_reason');

        if (in_array($finishReason, ['length', 'max_tokens'], true)) {
            Log::error('AI response was truncated by the provider.', [
                'step'            => $stepName,
                'finish_reason'   => $finishReason,
                'content_length'  => strlen($content),
                'content_preview' => $this->preview($content),
            ]);

            throw new Exception('The AI response was cut off before it was complete. Retry.');
        }

        return $content;
    }

    private function decodeJsonObject(string $content, string $stepName): array
    {
        $normalized = $this->normalizeJsonText($content);
        $decoded    = json_decode($normalized, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('AI response was not valid JSON.', array_merge(
                ['step' => $stepName, 'error' => json_last_error_msg()],
                $this->responseDiagnostics($normalized),
            ));

            throw new Exception('The AI response was not valid JSON. ' . json_last_error_msg());
        }

        if (! is_array($decoded)) {
            Log::error('AI response was not a JSON object or array.', array_merge(
                ['step' => $stepName, 'type' => get_debug_type($decoded)],
                $this->responseDiagnostics($normalized),
            ));

            throw new Exception(sprintf('The AI response was not valid JSON. Expected a JSON object or array, got %s.', get_debug_type($decoded)));
        }

        if ($decoded === []) {
            throw new Exception('The AI response was not usable for this step. The AI returned an empty JSON object.');
        }

        return $decoded;
    }

    private function normalizeJsonText(string $content): string
    {
        $normalized = trim($content);

        for ($depth = 0; $depth < self::MAX_FENCE_UNWRAP_DEPTH; $depth++) {
            if ($this->decodes($normalized)) {
                return $normalized;
            }

            $unwrapped = $this->unwrapCodeFence($normalized);

            if ($unwrapped === null) {
                break;
            }

            $normalized = $unwrapped;
        }

        if ($this->decodes($normalized)) {
            return $normalized;
        }

        return $this->extractOutermostJsonValue($normalized) ?? $normalized;
    }

    private function decodes(string $content): bool
    {
        if ($content === '') {
            return false;
        }

        json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private function unwrapCodeFence(string $content): ?string
    {
        if (! preg_match('/\A```[A-Za-z0-9_+-]*[ \t]*\R/', $content, $open)) {
            return null;
        }

        $body = substr($content, strlen($open[0]));

        if (! preg_match('/\R?```[ \t]*\z/', $body, $close, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $body = substr($body, 0, $close[0][1]);

        return trim($body) === '' ? null : trim($body);
    }

    private function extractOutermostJsonValue(string $content): ?string
    {
        $length = strlen($content);

        for ($start = 0; $start < $length; $start++) {
            if ($content[$start] !== '{' && $content[$start] !== '[') {
                continue;
            }

            $candidate = $this->readBalanced($content, $start);

            if ($candidate !== null && $this->decodes($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function readBalanced(string $content, int $start): ?string
    {
        $length    = strlen($content);
        $depth     = 0;
        $inString  = false;
        $escaped   = false;

        for ($index = $start; $index < $length; $index++) {
            $char = $content[$index];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;

                continue;
            }

            if ($char === '{' || $char === '[') {
                $depth++;
            } elseif ($char === '}' || $char === ']') {
                $depth--;

                if ($depth === 0) {
                    return substr($content, $start, $index - $start + 1);
                }

                if ($depth < 0) {
                    return null;
                }
            }
        }

        return null;
    }

    private function responseDiagnostics(string $content): array
    {
        return [
            'content_length'  => strlen($content),
            'starts_with'     => substr($content, 0, 40),
            'ends_with'       => substr($content, -40),
            'content_preview' => $this->preview($content),
        ];
    }

    private function preview(string $content): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($content)) ?? trim($content);

        if (mb_strlen($collapsed) <= self::PREVIEW_LENGTH * 2) {
            return $collapsed;
        }

        return mb_substr($collapsed, 0, self::PREVIEW_LENGTH).' … '.mb_substr($collapsed, -self::PREVIEW_LENGTH);
    }

    private function post(string $stepName, string $endpoint, string $apiKey, array $payload, int $timeout): mixed
    {
        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($endpoint, $payload);
        } catch (\Throwable $exception) {
            throw $this->transportFailure($stepName, $exception);
        }

        if (! $response->successful()) {
            $status  = $response->status();
            $message = match (true) {
                $status === 429 => 'The AI provider rate limit was reached. Wait a moment and retry.',
                $status >= 500  => sprintf('The AI provider returned a server error (HTTP %d). Retry later.', $status),
                default         => sprintf('The AI provider rejected the request (HTTP %d). Check the model name and API key.', $status),
            };

            Log::error('AI provider responded with an error.', [
                'step'         => $stepName,
                'status'       => $status,
                'body_excerpt' => mb_substr((string) $response->body(), 0, 300),
            ]);

            throw new Exception($message);
        }

        return $response->json();
    }

    private function transportFailure(string $stepName, \Throwable $exception): Exception
    {
        Log::error('AI transport failure.', [
            'step'      => $stepName,
            'exception' => $exception::class,
            'detail'    => $this->safeMessage($exception),
        ]);

        if ($this->isTimeoutException($exception)) {
            return new Exception('The AI Brain did not finish in time. Retry, or choose an AI Brain with a longer timeout.', 0, $exception);
        }

        return new Exception('The AI provider could not be reached. Check the AI Brain URL, network, and API key.', 0, $exception);
    }

    private function isTimeoutException(\Throwable $exception): bool
    {
        if (! method_exists($exception, 'getCode')) {
            return false;
        }

        if ((int) $exception->getCode() === 28) {
            return true;
        }

        $message = $exception->getMessage();

        return stripos($message, 'timed out') !== false
            || stripos($message, 'timeout') !== false
            || stripos($message, 'timedout') !== false;
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

    private function foundationResponseFormat(array $decoded, string $stepName): array
    {
        $title = $decoded['novel_title'] ?? null;

        if (! is_string($title) || trim($title) === '') {
            throw new Exception('The AI response was not usable for this step. It contained no novel title. Retry.');
        }

        return [
            'title' => trim($title),
            'subtitle' => trim((string) ($decoded['novel_subtitle'] ?? '')),
            'foundation' => $this->formatAsObject($decoded['novel_foundation'] ?? []),
            'characters' => [
                'characters' => $this->formatAsList($decoded['characters']['characters'] ?? []),
                'relationships' => $this->formatAsList($decoded['characters']['relationships'] ?? []),
            ],
            'world_bible' => $this->formatAsObject($decoded['world_bible'] ?? []),
            'locations' => $this->formatAsObject($decoded['locations'] ?? []),
            'factions' => $this->formatAsObject($decoded['factions'] ?? []),
            'creatures' => $this->formatAsObject($decoded['creatures'] ?? []),
            'systems' => $this->formatAsObject($decoded['systems'] ?? []),
            'timeline' => $this->formatAsObject($decoded['timeline'] ?? []),
        ];
    }

    private function planChapterResponseFormat(array $decoded, string $stepName): array
    {
        $chapterPlan = $this->formatAsList($decoded['chapter_plan'] ?? []);

        if ($chapterPlan === []) {
            throw new Exception('The AI response was not usable for this step. It contained no chapter plan, so no chapters could be created. Retry.');
        }

        return [
            'story_structure' => $this->formatAsObject($decoded['story_structure'] ?? []),
            'twists_and_foreshadowing' => $this->formatAsObject($decoded['twists_and_foreshadowing'] ?? []),
            'scene_plans' => $this->formatAsList($decoded['scene_plans'] ?? []),
            'dialogue_plans' => $this->formatAsList($decoded['dialogue_plans'] ?? []),
            'chapter_plan' => $chapterPlan,
            'page_plan' => $this->formatAsList($decoded['page_plan'] ?? []),
            'chapter_summaries' => $this->formatAsList($decoded['chapter_summaries'] ?? []),
        ];
    }

    private function chapterContentResponseFormat(array $decoded, string $stepName): array
    {
        $content = $decoded['chapter_content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            throw new Exception('The AI response was not usable for this step. It contained no chapter content. Retry.');
        }

        return [
            'chapter_content' => trim($content),
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
            'title' => $novelChapter->title,
            'summary' => $novelChapter->summery,
            'scenes' => [],
            'chapter_goals' => [],
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
