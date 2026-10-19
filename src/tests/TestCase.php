<?php

namespace Tests;

use App\Services\LayoutService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function primeAiBrainAvailability(): void
    {
        foreach (DB::table('ai_brains')->pluck('id') as $id) {
            Cache::put(LayoutService::availabilityCacheKey((int) $id), true, now()->addMinute());
        }
    }
}
