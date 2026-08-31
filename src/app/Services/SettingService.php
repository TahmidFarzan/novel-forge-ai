<?php

namespace App\Services;

use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingService
{
    public function new(): Setting
    {
        return new Setting();
    }

    public function find(string $slug): Setting
    {
        return Setting::with([
            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function findByName(string $name): Setting
    {
        return Setting::with([
            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('name', $name)->firstOrFail();
    }

    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Setting::query();

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

    public function save(SettingRequest $request, Setting $setting): array
    {
        try {
            DB::transaction(function () use ($request, $setting) {
                $setting->options = $request->input('options', []);
                $setting->save();
            });
            return ['status' => 'success', 'message' => 'Setting updated successfully.'];
        } catch (Exception $exception) {
            Log::error('Failed to update setting.', ['exception' => $exception]);
            return ['status' => 'error', 'message' => 'Failed to update setting. Please try again.'];
        }
    }
}
