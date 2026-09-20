<?php

namespace App\Services\BackOffice;

use App\Helpers\AiPromptGeneratorHelper;
use App\Models\AiBrain;
use App\Models\AiPrompt;
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

    public function new(): NovelChapter
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

    public function findByNo(Novel $novel, string|int $no): NovelChapter
    {
        return NovelChapter::with([
            'novel',

            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('novel_id', $novel->id)->where('no', $no)->firstOrFail();
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

    public function generateSummaries(Novel $novel, string $step, AiPrompt $aiPrompt, AiBrain $aiBrain): void
    {
        $chapterPlan = $novel->chapter_plan ?? [];

        if (! is_array($chapterPlan) || empty($chapterPlan)) {
            throw new Exception('Chapter plan is empty.');
        }

        foreach ($chapterPlan as $chapterPlanEntry) {
            $this->generateSummary($novel, $step, (array) $chapterPlanEntry, $aiPrompt, $aiBrain);
        }
    }

    private function generateSummary(Novel $novel, string $step, array $chapterPlanEntry, AiPrompt $aiPrompt, AiBrain $aiBrain): void
    {
        $formatedInput = $this->huggingFaceApiService->step15ChapterSummaryRequestInputsFormatter($novel, $chapterPlanEntry);
        $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
        $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

        $this->saveSummaryByNovel($novel, $chapterPlanEntry, $stepData['chapter_summary']);
    }

    public function generateStep16(Novel $novel, NovelChapter $novelChapter,  string $step,AiPrompt $aiPrompt, AiBrain $aiBrain): void
    {

        $formatedInput = $this->huggingFaceApiService->step16ChapterContentRequestInputsFormatter($novel,$novelChapter);
        $fullPrompt = AiPromptGeneratorHelper::generateFullPrompt($aiPrompt->prompt, $formatedInput);
        $stepData = $this->huggingFaceApiService->sendPostRequest($step, $aiBrain->api_url, $aiBrain->api_key, $aiBrain->model,  $fullPrompt, $aiBrain->max_output_tokens, $aiBrain->timeout_seconds);

        $this->saveContentByNovel($novelChapter, $stepData['chapter_content']);
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

    private function wordCount(string $content): int
    {
        $words = preg_split('/\s+/u', trim($content));

        return is_array($words)
            ? count(array_filter($words))
            : 0;
    }
}
