<?php
namespace App\Services;

use App\Models\AiBrain;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class LayoutService
{
    protected const DEFAULT_PER_PAGE = 40;

    protected const AVAILABILITY_CACHE_TTL_SECONDS = 60;
    protected const AVAILABILITY_CONNECT_TIMEOUT = 2;
    protected const AVAILABILITY_TIMEOUT = 3;

    public function getAvailableAiBrains(Request $request): array
    {
        $records = AiBrain::query()
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', self::DEFAULT_PER_PAGE));

        $availability = $this->resolveAvailability($records->items());

        $items = $records
            ->map(fn (AiBrain $aiBrain) => $this->mapToAvailableAiBrain(
                $aiBrain,
                $availability[$aiBrain->id] ?? false
            ))
            ->values();

        return [
            'items'         => $items,
            'total'         => $records->total(),
            'current_page'  => $records->currentPage(),
            'last_page'     => $records->lastPage(),
        ];
    }

    private function mapToAvailableAiBrain(AiBrain $aiBrain, bool $isActive): array
    {
        return [
            'id'         => $aiBrain->id,
            'name'       => $aiBrain->name,
            'slug'       => $aiBrain->slug,
            'model_name' => $aiBrain->model,
            'profile'    => $aiBrain->provider,
            'is_active'  => $isActive,
        ];
    }

    private function resolveAvailability(array $aiBrains): array
    {
        $availability = [];

        foreach ($aiBrains as $aiBrain) {
            $availability[$aiBrain->id] = Cache::get(
                $this->availabilityCacheKey($aiBrain->id),
                false
            );
        }

        $uncached = collect($aiBrains)
            ->reject(fn (AiBrain $aiBrain) => Cache::has($this->availabilityCacheKey($aiBrain->id)))
            ->values();

        if ($uncached->isNotEmpty()) {
            foreach ($this->resolveBatchAvailability($uncached->all()) as $id => $isActive) {
                Cache::put(
                    $this->availabilityCacheKey($id),
                    $isActive,
                    now()->addSeconds(self::AVAILABILITY_CACHE_TTL_SECONDS)
                );

                $availability[$id] = $isActive;
            }
        }

        return $availability;
    }

    private function resolveBatchAvailability(array $aiBrains): array
    {
        $availability = [];
        $checkable = [];

        foreach ($aiBrains as $aiBrain) {
            $apiUrl = trim((string) $aiBrain->api_url);

            if ($apiUrl === '') {
                $availability[$aiBrain->id] = false;

                continue;
            }

            $checkable[] = $aiBrain;
        }

        if ($checkable === []) {
            return $availability;
        }

        try {
            $responses = Http::pool(fn (Pool $pool) => collect($checkable)->map(
                fn (AiBrain $aiBrain) => $pool
                    ->connectTimeout(self::AVAILABILITY_CONNECT_TIMEOUT)
                    ->timeout(self::AVAILABILITY_TIMEOUT)
                    ->acceptJson()
                    ->get(rtrim((string) $aiBrain->api_url, '/'))
            )->all());

            foreach ($checkable as $key => $aiBrain) {
                $response = $responses[$key] ?? null;

                $availability[$aiBrain->id] = $response instanceof Response && $response->successful();
            }
        } catch (Throwable $exception) {
            foreach ($checkable as $aiBrain) {
                $availability[$aiBrain->id] = false;
            }
        }

        return $availability;
    }

    private function availabilityCacheKey(int $id): string
    {
        return "ai_brain_availability_{$id}";
    }
}
