<?php
namespace Database\Seeders;

use App\Models\AiBrain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiBrainSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            AiBrain::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='ai_brains'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            AiBrain::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            AiBrain::truncate();
        }

        foreach ($this->getAiBrainsFromStaticData() as $aiBrain) {
            AiBrain::factory()->state([
                'name'              => $aiBrain->name,
                'api_url'           => $aiBrain->api_url,
                'api_key'           => $aiBrain->api_key,
                'brief'             => $aiBrain->brief ?? null,
                'focus'             => $aiBrain->focus ?? null,
                'context_window'    => $aiBrain->context_window,
                'average_latency'   => $aiBrain->average_latency,
                'minimum_wait_time' => $aiBrain->minimum_wait_time,
                'timeout_seconds'   => $aiBrain->timeout_seconds,
                'max_output_tokens' => $aiBrain->max_output_tokens ?? null,
            ])->create();
        }
    }

    private function getAiBrainsFromStaticData()
    {
        return collect([

            (object) [
                'name'              => 'Google: Gemma 4 26B A4B',
                "model"             => "google/gemma-4-26b-a4b-it:free",
                'api_url'           => 'https://openrouter.ai/api/v1',
                'api_key'           => 'sk-your-openrouter-api-key',
                'brief'             => 'AI writing model for generating documents, workbooks, ebooks and structured educational content.',
                'focus'             => 'Premium document generation, chapter writing, workbook creation, story generation, educational materials',
                'context_window'    => 262000,
                'average_latency'   => 0.90,
                'minimum_wait_time' => 2,
                'timeout_seconds'   => 60,
                'max_output_tokens' => 5000,
            ],

        ]);
    }
}
