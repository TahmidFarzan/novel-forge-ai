<?php
namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Models\AiBrain;
use App\Models\Novel;
use App\Models\NovelChapter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelChapterService
{
    protected HuggingFaceApiService $huggingFaceApiService;

    public function __construct(HuggingFaceApiService $huggingFaceApiService)
    {
        $this->huggingFaceApiService = $huggingFaceApiService;
    }

    public function new (): NovelChapter
    {
        return new NovelChapter();
    }

    public function find(Novel $novel, string $slug): NovelChapter
    {
        return NovelChapter::with([
            'novel',

            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('novel_id', $novel->id)->where('slug', $slug)->firstOrFail();
    }

    public function search(Novel $novel, Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = NovelChapter::query();

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
                'no',
                'title',
            ], 'like', $likeSearch);
        }

        if ($request->filled('genre_id')) {
            $query->whereHas(
                'genres',
                fn($query) => $query->where('genres.id', $request->input('genre_id'))
            );
        }

        $query->where('novel_id', $novel->id);

        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

    public function generateSummary(Novel $novel, array $context, array $chapterPlanEntry, string $prompt, AiBrain $aiBrain, string $additionalInformation): void
    {
        $scenePlans = $this->chapterScenePlans($novel, $chapterPlanEntry);

        $requestInputs = $this->chapterSummaryRequestInputsFormatter($context, $chapterPlanEntry, $scenePlans, $additionalInformation);
        $fullPrompt    = AiPromptGeneratorHelper::generateFullPrompt($prompt, $requestInputs);

        $apiResponse = $this->huggingFaceApiService->sendPostRequest($aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

        $summery = $this->extractChapterSummaryFromResponse($apiResponse);

        $this->saveSummaryByNovel($novel, $chapterPlanEntry, $summery);
    }

    public function generateContent(Novel $novel, NovelChapter $novelChapter, array $context, string $prompt, AiBrain $aiBrain, string $additionalInformation): void
    {
        if (is_string($novelChapter->content) && trim($novelChapter->content) !== '') {
            return;
        }

        $summery = $novelChapter->summery;

        if (! is_string($summery) || trim($summery) === '') {
            throw new Exception('Chapter ' . $novelChapter->no . ' does not have a summary. Generate chapter summaries first.');
        }

        $chapterPlanEntry = $this->findChapterPlanEntry($novel, $novelChapter);
        $scenePlans       = $this->chapterScenePlans($novel, $chapterPlanEntry);
        $dialoguePlans    = $this->chapterDialoguePlans($novel, $scenePlans);

        $requestInputs = $this->chapterContentRequestInputsFormatter($context, $novelChapter, $chapterPlanEntry, $scenePlans, $dialoguePlans, $additionalInformation);
        $fullPrompt    = AiPromptGeneratorHelper::generateFullPrompt($prompt, $requestInputs);

        $apiResponse = $this->huggingFaceApiService->sendPostRequest($aiBrain->api_url, $aiBrain->api_key, $aiBrain->model, $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

        $content = $this->extractChapterContentFromResponse($apiResponse);

        $this->saveContentByNovel($novelChapter, $content);
    }

    public function saveSummaryByNovel(Novel $novel, array $chapterPlanEntry, string $summery): void
    {
        DB::transaction(function () use ($novel, $chapterPlanEntry, $summery) {
            $novelChapter = NovelChapter::query()
                ->where('novel_id', $novel->id)
                ->where('no', (string) ($chapterPlanEntry['chapter_number'] ?? ''))
                ->first();

            if (! $novelChapter) {
                $novelChapter                  = new NovelChapter();
                $novelChapter->novel_id        = $novel->id;
                $novelChapter->no              = (string) ($chapterPlanEntry['chapter_number'] ?? '');
                $novelChapter->created_by_id   = Auth::id();
            }

            $novelChapter->title   = (string) ($chapterPlanEntry['title'] ?? '');
            $novelChapter->summery = $summery;

            $novelChapter->save();
        });
    }

    public function saveContentByNovel(NovelChapter $novelChapter, string $content): void
    {
        DB::transaction(function () use ($novelChapter, $content) {
            $novelChapter->content    = $content;
            $novelChapter->word_count = $this->wordCount($content);

            $novelChapter->save();
        });
    }

    public function hasSummary(NovelChapter $novelChapter): bool
    {
        return is_string($novelChapter->summery) && trim($novelChapter->summery) !== '';
    }

    public function hasContent(NovelChapter $novelChapter): bool
    {
        return is_string($novelChapter->content) && trim($novelChapter->content) !== '';
    }

    public function isGenerated(NovelChapter $novelChapter): bool
    {
        return $this->hasSummary($novelChapter) && $this->hasContent($novelChapter);
    }

    public function delete(Novel $novel, NovelChapter $novelChapter): array
    {

        try {

            DB::transaction(function () use ($novelChapter) {
                $novelChapter->delete();
            });

            return [
                'status'  => 'success',
                'message' => 'Novel chapter deleted successfully.',
            ];
        } catch (Exception $exception) {

            Log::error('Novel delete failed.', [
                'exception' => $exception,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to delete novel chapter. Please try again.',
            ];
        }
    }

    private function chapterSummaryRequestInputsFormatter(array $context, array $chapterPlanEntry, array $scenePlans, string $additionalInformation): array
    {
        return [
            "foundation"                => $context['foundation'],
            "characters"                => $context['characters'],
            "story_structure"           => $context['story_structure'],
            "twists_and_foreshadowing"  => $context['twists_and_foreshadowing'],
            "chapter_plan_entry"        => json_encode($chapterPlanEntry, JSON_PRETTY_PRINT),
            "scene_plans"               => json_encode($scenePlans, JSON_PRETTY_PRINT),
            "additional_information"    => $additionalInformation,
        ];
    }

    private function chapterContentRequestInputsFormatter(array $context, NovelChapter $novelChapter, array $chapterPlanEntry, array $scenePlans, array $dialoguePlans, string $additionalInformation): array
    {
        return [
            "language"                  => $context['language'],
            "foundation"                => $context['foundation'],
            "characters"                => $context['characters'],
            "world_bible"               => $context['world_bible'],
            "story_structure"           => $context['story_structure'],
            "twists_and_foreshadowing"  => $context['twists_and_foreshadowing'],
            "chapter_summary"           => $novelChapter->summery,
            "chapter_plan_entry"        => json_encode($chapterPlanEntry, JSON_PRETTY_PRINT),
            "scene_plans"               => json_encode($scenePlans, JSON_PRETTY_PRINT),
            "dialogue_plans"            => json_encode($dialoguePlans, JSON_PRETTY_PRINT),
            "additional_information"    => $additionalInformation,
        ];
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
            'pacing_and_flow'=> '',
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

    private function extractChapterSummaryFromResponse($apiResponse): string
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

        return json_encode($decoded['chapter_summary'] ?? [], JSON_UNESCAPED_UNICODE);
    }

    private function extractChapterContentFromResponse($apiResponse): string
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

            return trim($candidate);
        }

        if ($content === '') {
            throw new Exception('AI response is empty.');
        }

        return $content;
    }

    private function wordCount(string $content): int
    {
        $words = preg_split('/\s+/u', trim($content));

        return is_array($words)
            ? count(array_filter($words))
            : 0;
    }

}