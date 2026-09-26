<?php

namespace App\Services\BackOffice;

use App\Http\Requests\StoryBookStep1;
use App\Models\Novel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelService
{
    protected NovelGeneratorService $novelGeneratorService;

    public function __construct(NovelGeneratorService $novelGeneratorService)
    {
        $this->novelGeneratorService = $novelGeneratorService;
    }

    public function new(): Novel
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
            'aiBrain',

            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',

            'novelChapters',
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

    public function createFromFoundation(StoryBookStep1 $request): array
    {
        return $this->novelGeneratorService->createNovelFromFoundation($request);
    }

    public function generate(Novel $novel): array
    {
        return $this->novelGeneratorService->continueGeneration($novel);
    }

    public function stop(Novel $novel): array
    {
        return $this->novelGeneratorService->stop($novel);
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
}