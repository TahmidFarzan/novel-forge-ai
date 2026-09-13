<?php

namespace Database\Seeders;

use App\Models\AiBrain;
use App\Models\AiBrainOutputType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiBrainAiBrainOutputTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::table('ai_brain_ai_brain_output_type')->delete();
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('ai_brain_ai_brain_output_type')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            DB::table('ai_brain_ai_brain_output_type')->truncate();
        }

        $mapping = [
            'google-gemma-4-26b-a4b' => [
                'text',
            ],
        ];

        foreach ($mapping as $aiBrainSlug => $aiBrainOutputTypeSlugs) {

            $aiBrain = AiBrain::where('slug', $aiBrainSlug)->first();

            if (! $aiBrain) {
                continue;
            }

            $aiBrainOutputTypeIds = AiBrainOutputType::whereIn('slug', $aiBrainOutputTypeSlugs)
                ->pluck('id')
                ->toArray();

            $aiBrain->aiBrainOutputTypes()->sync($aiBrainOutputTypeIds);
        }
    }
}