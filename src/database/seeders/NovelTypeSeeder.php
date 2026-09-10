<?php

namespace Database\Seeders;

use App\Helpers\SeederHelper;
use App\Models\NovelType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NovelTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            NovelType::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='novel_types'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            NovelType::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            NovelType::truncate();
        }

        foreach (SeederHelper::novelTypes() as $novelType) {

            NovelType::factory()->state([
                'name' => $novelType->name,
                'brief' => $novelType->brief ?? null,
                'prompt_instruction' => $novelType->prompt_instruction ?? null,
            ])->create();

        }
    }
}