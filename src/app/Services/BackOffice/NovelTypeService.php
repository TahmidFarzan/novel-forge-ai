<?php
namespace App\Services\BackOffice;

use App\Http\Requests\NovelTypeRequest;
use App\Models\NovelType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NovelTypeService
{
    public function new (): NovelType
    {
        return new NovelType;
    }

    public function find(string $slug): NovelType
    {
        return NovelType::with([
            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function findByIdsOrRandom($ids = null)
    {
        if (empty($ids)) {
            return NovelType::inRandomOrder()
                ->limit(rand(2, 3))
                ->get();
        }

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        return NovelType::whereIn('id', $ids)->get();
    }
    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = NovelType::query();

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
                'brief',
            ], 'like', $likeSearch);
        }

        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->all());
    }

    public function save(NovelTypeRequest $request, NovelType $novelType): array
    {
        $isNew       = empty($novelType->id);
        $statusEvent = $isNew ? 'save' : 'update';

        try {

            DB::transaction(function () use ($request, $novelType, $isNew) {
                $novelType->name               = $request->input('name');
                $novelType->brief              = $request->input('brief');
                $novelType->prompt_instruction = $request->input('prompt_instruction');
                $novelType->created_by_id      = $isNew ? Auth::id() : $novelType->created_by_id;

                $novelType->save();
            });

            return [
                'status'  => 'success',
                'message' => $isNew ? 'Novel type created successfully.' : 'Novel type updated successfully.',
            ];
        } catch (Exception $exception) {
            Log::error("Failed to {$statusEvent} novel type.", [
                'exception' => $exception,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to save novel type. Please try again.',
            ];
        }
    }

    public function delete(NovelType $novelType): array
    {

        try {

            DB::transaction(function () use ($novelType) {
                $novelType->delete();
            });

            return [
                'status'  => 'success',
                'message' => 'Novel type deleted successfully.',
            ];
        } catch (Exception $exception) {

            Log::error('Novel type delete failed.', [
                'exception' => $exception,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to delete novel type. Please try again.',
            ];
        }
    }
}