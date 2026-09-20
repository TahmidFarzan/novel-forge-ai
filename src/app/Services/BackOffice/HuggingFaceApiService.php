<?php

namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use Exception;
use App\Models\Novel;
use App\Models\NovelChapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceApiService
{
    protected int $defaultTimeout = 120;

    public function sendPostRequest(string $stepName, string $url, string $apiKey, string $model, mixed $data = null, ?int $maxOutputTokens = null, ?int $timeout = null): array
    {
        $requestTimeout = $timeout ?? $this->defaultTimeout;

        set_time_limit($requestTimeout);

        $payload = $this->buildPayload(
            $model,
            $data,
            $maxOutputTokens
        );

        $endpoint = rtrim($url, '/');

        $response = Http::timeout(
            $requestTimeout
        )
            ->withToken($apiKey)
            ->acceptJson()
            ->post(
                $endpoint,
                $payload
            );

        if (! $response->successful()) {
            throw new Exception(
                $response->body()
            );
        }


        return $this->aiResponseFormats($stepName,  $response->json());
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


    public function aiResponseFormats(string $stepName, array $apiResponse): array
    {
        return match ($stepName) {
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1     => $this->apiStep1ResponseFormat($this->extractStep1FoundationFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2     => $this->apiStep2ResponseFormat($this->extractStep2CharactersFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP3     => $this->apiStep3ResponseFormat($this->extractStep3WorldBibleFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP4     => $this->apiStep4ResponseFormat($this->extractStep4LocationsFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP5     => $this->apiStep5ResponseFormat($this->extractStep5FactionsFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP6     => $this->apiStep6ResponseFormat($this->extractStep6CreaturesFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP7     => $this->apiStep7ResponseFormat($this->extractStep7SystemsFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP8     => $this->apiStep8ResponseFormat($this->extractStep8TimelineFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP9     => $this->apiStep9ResponseFormat($this->extractStep9StoryStructureFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP10    => $this->apiStep10ResponseFormat($this->extractStep10TwistsAndForeshadowingFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP11    => $this->apiStep11ResponseFormat($this->extractStep11ScenePlansFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP12    => $this->apiStep12ResponseFormat($this->extractStep12DialoguePlansFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13    => $this->apiStep13ResponseFormat($this->extractStep13ChapterPlanFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP14    => $this->apiStep14ResponseFormat($this->extractStep14PagePlanFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15    => $this->apiStep15ResponseFormat($this->extractStep15ChapterSummaryFromResponse($apiResponse)),
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16    => $this->apiStep16ResponseFormat($this->extractStep16ChapterContentFromResponse($apiResponse)),
            default                                                                           => throw new Exception("Unknown AI step name [{$stepName}]."),
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
            "foundation"             => json_encode($novel->foundation ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step3InputsFormatter(Novel $novel): array
    {
        return [
            "foundation"             => json_encode($novel->foundation ?? [], JSON_PRETTY_PRINT),
            "characters"             => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step4InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "characters"             => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step5InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "locations"              => json_encode($novel->locations ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step6InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "locations"              => json_encode($novel->locations ?? [], JSON_PRETTY_PRINT),
            "factions"               => json_encode($novel->factions ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step7InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "creatures"              => json_encode($novel->creatures ?? [], JSON_PRETTY_PRINT),
            "factions"               => json_encode($novel->factions ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step8InputsFormatter(Novel $novel): array
    {
        return [
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "factions"               => json_encode($novel->factions ?? [], JSON_PRETTY_PRINT),
            "foundation"             => json_encode($novel->foundation ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step9InputsFormatter(Novel $novel): array
    {
        return [
            "foundation"             => json_encode($novel->foundation ?? [], JSON_PRETTY_PRINT),
            "characters"             => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "timeline"               => json_encode($novel->timeline ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step10InputsFormatter(Novel $novel): array
    {
        return [
            "story_structure"        => json_encode($novel->story_structure ?? [], JSON_PRETTY_PRINT),
            "characters"             => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
            "world_bible"            => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step11InputsFormatter(Novel $novel): array
    {
        return [
            "story_structure"          => json_encode($novel->story_structure ?? [], JSON_PRETTY_PRINT),
            "twists_and_foreshadowing" => json_encode($novel->twists_and_foreshadowing ?? [], JSON_PRETTY_PRINT),
            "locations"                => json_encode($novel->locations ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step12InputsFormatter(Novel $novel): array
    {
        return [
            "characters"             => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
            "scene_plans"            => json_encode($novel->scene_plans ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step13InputsFormatter(Novel $novel): array
    {
        return [
            "scene_plans"            => json_encode($novel->scene_plans ?? [], JSON_PRETTY_PRINT),
            "story_structure"        => json_encode($novel->story_structure ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step14InputsFormatter(Novel $novel): array
    {
        return [
            "chapter_plan"           => json_encode($novel->chapter_plan ?? [], JSON_PRETTY_PRINT),
            "scene_plans"            => json_encode($novel->scene_plans ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step15InputsFormatter(Novel $novel, $chapterPlanEntry): array
    {
        $scenePlans = $this->chapterScenePlans($novel, $chapterPlanEntry);

        return [
            "foundation"               => json_encode($novel->foundation ?? [], JSON_PRETTY_PRINT),
            "characters"               => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
            "story_structure"          => json_encode($novel->story_structure ?? [], JSON_PRETTY_PRINT),
            "twists_and_foreshadowing" => json_encode($novel->twists_and_foreshadowing ?? [], JSON_PRETTY_PRINT),
            "chapter_plan_entry"       => json_encode($chapterPlanEntry ?? [], JSON_PRETTY_PRINT),
            "scene_plans"              => json_encode($scenePlans ?? [], JSON_PRETTY_PRINT),
        ];
    }

    public function step16InputsFormatter(Novel $novel, NovelChapter $novelChapter): array
    {

        $chapterPlanEntry = $this->findChapterPlanEntry($novel, $novelChapter);
        $scenePlans       = $this->chapterScenePlans($novel, $chapterPlanEntry);
        $dialoguePlans    = $this->chapterDialoguePlans($novel, $scenePlans);

        return [
            "language"                 => $novel->language?->name ?? '',
            "foundation"               => json_encode($novel->foundation ?? [], JSON_PRETTY_PRINT),
            "characters"               => json_encode($novel->characters ?? [], JSON_PRETTY_PRINT),
            "world_bible"              => json_encode($novel->world_bible ?? [], JSON_PRETTY_PRINT),
            "story_structure"          => json_encode($novel->story_structure ?? [], JSON_PRETTY_PRINT),
            "twists_and_foreshadowing" => json_encode($novel->twists_and_foreshadowing ?? [], JSON_PRETTY_PRINT),
            "chapter_summary"          => $novel->chapter_summary ?? '',
            "chapter_plan_entry"       => json_encode($chapterPlanEntry ?? [], JSON_PRETTY_PRINT),
            "scene_plans"              => json_encode($scenePlans ?? [], JSON_PRETTY_PRINT),
            "dialogue_plans"           => json_encode($dialoguePlans ?? [], JSON_PRETTY_PRINT),
        ];
    }

    private function extractStepContentFromResponse(array $apiResponse): string
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

        return $content;
    }

    private function decodeStepContent(string $content): array
    {
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

        return $decoded;
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

    private function extractStep1FoundationFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'title'      => $decoded['novel_title'] ?? null,
            'subtitle'   => $decoded['novel_subtitle'] ?? null,
            'foundation' => $decoded['novel_foundation'] ?? null,
        ];
    }

    private function extractStep2CharactersFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'characters'            => $decoded['characters'] ?? [],
            'relationship_dynamics' => $decoded['relationships'] ?? [],
        ];
    }

    private function extractStep3WorldBibleFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'world_bible' => $decoded['world_bible'] ?? [],
        ];
    }

    private function extractStep4LocationsFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'locations' => $decoded['locations'] ?? [],
        ];
    }

    private function extractStep5FactionsFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'factions' => $decoded['factions'] ?? [],
        ];
    }

    private function extractStep6CreaturesFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'creatures' => $decoded['creatures'] ?? [],
        ];
    }

    private function extractStep7SystemsFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'systems' => $decoded['systems'] ?? [],
        ];
    }

    private function extractStep8TimelineFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'timeline' => $decoded['timeline'] ?? [],
        ];
    }

    private function extractStep9StoryStructureFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'story_structure' => $decoded['story_structure'] ?? [],
        ];
    }

    private function extractStep10TwistsAndForeshadowingFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'twists_and_foreshadowing' => $decoded['twists_and_foreshadowing'] ?? [],
        ];
    }

    private function extractStep11ScenePlansFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'scene_plans' => $decoded['scene_plans'] ?? [],
        ];
    }

    private function extractStep12DialoguePlansFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'dialogue_plans' => $decoded['dialogue_plans'] ?? [],
        ];
    }

    private function extractStep13ChapterPlanFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'chapter_plan' => $decoded['chapter_plan'] ?? [],
        ];
    }

    private function extractStep14PagePlanFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'page_plan' => $decoded['page_plan'] ?? [],
        ];
    }

    private function extractStep15ChapterSummaryFromResponse(array $apiResponse): array
    {
        $decoded = $this->decodeStepContent($this->extractStepContentFromResponse($apiResponse));

        return [
            'chapter_summary' => $decoded['chapter_summary'] ?? [],
        ];
    }

    private function extractStep16ChapterContentFromResponse(array $apiResponse): array
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
            '/^```(?:json|markdown|md|txt)?\s*|\s*```$/i',
            '',
            $content
        );

        $content = trim($content);

        $decoded = json_decode(
            $content,
            true
        );

        if (is_array($decoded)) {
            $candidate = $decoded['chapter_content'] ?? null;

            if (! is_string($candidate) || trim($candidate) === '') {
                throw new Exception('AI response does not contain chapter content.');
            }

            return [
                'chapter_content' => trim($candidate),
            ];
        }

        if ($content === '') {
            throw new Exception('AI response is empty.');
        }

        return [
            'chapter_content' => $content,
        ];
    }

    private function apiStep1ResponseFormat(array $extracted): array
    {
        return [
            'title'      => $extracted['title'],
            'subtitle'   => $extracted['subtitle'],
            'foundation' => $extracted['foundation'],
        ];
    }

    private function apiStep2ResponseFormat(array $extracted): array
    {
        return [
            'characters'            => $this->formatAsArrayField($extracted['characters'] ?? []),
            'relationship_dynamics' => $this->formatAsArrayField($extracted['relationship_dynamics'] ?? []),
        ];
    }

    private function apiStep3ResponseFormat(array $extracted): array
    {
        return [
            'world_bible' => $this->formatAsArrayField($extracted['world_bible'] ?? []),
        ];
    }

    private function apiStep4ResponseFormat(array $extracted): array
    {
        return [
            'locations' => $this->formatAsArrayField($extracted['locations'] ?? []),
        ];
    }

    private function apiStep5ResponseFormat(array $extracted): array
    {
        return [
            'factions' => $this->formatAsArrayField($extracted['factions'] ?? []),
        ];
    }

    private function apiStep6ResponseFormat(array $extracted): array
    {
        return [
            'creatures' => $this->formatAsArrayField($extracted['creatures'] ?? []),
        ];
    }

    private function apiStep7ResponseFormat(array $extracted): array
    {
        return [
            'systems' => $this->formatAsArrayField($extracted['systems'] ?? []),
        ];
    }

    private function apiStep8ResponseFormat(array $extracted): array
    {
        return [
            'timeline' => $this->formatAsArrayField($extracted['timeline'] ?? []),
        ];
    }

    private function apiStep9ResponseFormat(array $extracted): array
    {
        return [
            'story_structure' => $this->formatAsArrayField($extracted['story_structure'] ?? []),
        ];
    }

    private function apiStep10ResponseFormat(array $extracted): array
    {
        return [
            'twists_and_foreshadowing' => $this->formatAsArrayField($extracted['twists_and_foreshadowing'] ?? []),
        ];
    }

    private function apiStep11ResponseFormat(array $extracted): array
    {
        return [
            'scene_plans' => $this->formatAsArrayField($extracted['scene_plans'] ?? []),
        ];
    }

    private function apiStep12ResponseFormat(array $extracted): array
    {
        return [
            'dialogue_plans' => $this->formatAsArrayField($extracted['dialogue_plans'] ?? []),
        ];
    }

    private function apiStep13ResponseFormat(array $extracted): array
    {
        return [
            'chapter_plan' => $this->formatAsArrayField($extracted['chapter_plan'] ?? []),
        ];
    }

    private function apiStep14ResponseFormat(array $extracted): array
    {
        return [
            'page_plan' => $this->formatAsArrayField($extracted['page_plan'] ?? []),
        ];
    }

    private function apiStep15ResponseFormat(array $extracted): array
    {
        return [
            'chapter_summary' => json_encode($extracted['chapter_summary'] ?? [], JSON_UNESCAPED_UNICODE),
        ];
    }

    private function apiStep16ResponseFormat(array $extracted): array
    {
        return [
            'chapter_content' => (string) ($extracted['chapter_content'] ?? ''),
        ];
    }

    private function formatAsArrayField(mixed $value): array
    {
        return is_array($value) ? $value : (array) $value;
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
