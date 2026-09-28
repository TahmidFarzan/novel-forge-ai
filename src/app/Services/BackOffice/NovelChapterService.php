<?php

namespace App\Services\BackOffice;

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

    public function syncChaptersFromPlan(Novel $novel, array $chapterPlan, array $chapterSummaries): void
    {
        if (empty($chapterPlan)) {
            throw new Exception('Chapter plan is empty.');
        }

        $summaries = [];

        foreach ($chapterSummaries as $chapterSummary) {
            $no = (string) ((array) $chapterSummary)['chapter_number'] ?? '';

            if ($no !== '') {
                $summaries[$no] = (array) $chapterSummary;
            }
        }

        DB::transaction(function () use ($novel, $chapterPlan, $summaries) {
            foreach ($chapterPlan as $chapterPlanEntry) {
                $chapterPlanEntry = (array) $chapterPlanEntry;
                $no               = (string) ($chapterPlanEntry['chapter_number'] ?? '');

                if ($no === '') {
                    continue;
                }

                $summery = $this->encodeChapterSummary($summaries[$no] ?? [], $no);

                $novelChapter = NovelChapter::query()
                    ->where('novel_id', $novel->id)
                    ->where('no', $no)
                    ->first();

                if (! $novelChapter) {
                    $novelChapter                = new NovelChapter();
                    $novelChapter->novel_id      = $novel->id;
                    $novelChapter->no            = $no;
                    $novelChapter->created_by_id = Auth::id();
                }

                $novelChapter->title   = (string) ($chapterPlanEntry['title'] ?? $summaries[$no]['chapter_title'] ?? '');
                $novelChapter->summery = $summery;

                $novelChapter->save();
            }
        });
    }

    public function generateChapterContent(Novel $novel, NovelChapter $novelChapter, string $stepName, AiPrompt $aiPrompt, AiBrain $aiBrain): void
    {
        $stepData = $this->huggingFaceApiService->generate(
            $stepName,
            $aiBrain->api_url,
            $aiBrain->api_key,
            $aiBrain->model,
            $aiPrompt->prompt,
            $this->huggingFaceApiService->chapterContentInputs($novel, $novelChapter),
            $aiBrain->max_output_tokens,
            $aiBrain->timeout_seconds,
        );

        $this->saveContentByNovel($novelChapter, $stepData['chapter_content']);
    }

    private function encodeChapterSummary(array $chapterSummary, string $no): string
    {
        $summary = $chapterSummary['chapter_summary'] ?? null;

        if (! is_array($summary) || $summary === []) {
            return '';
        }

        $encoded = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($encoded)) {
            return '';
        }

        return $encoded;
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
