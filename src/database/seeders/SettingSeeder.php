<?php

namespace Database\Seeders;

use App\Helpers\SettingHelper;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            Setting::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='settings'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Setting::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            Setting::truncate();
        }

        $settings = [
            [
                'name' => SettingHelper::NAME_AI,
                'options' => [
                    SettingHelper::OPTION_AI_BRAIN => [
                        'valueType' => SettingHelper::OPTION_VALUE_TYPE_INTEGER,
                        'value' => 1,
                    ],
                    SettingHelper::OPTION_AI_BRAIN_RUNNER => [
                        'valueType' => SettingHelper::OPTION_VALUE_TYPE_INTEGER,
                        'value' => 1,
                    ],
                ],
            ],

        ];

        foreach ($settings as $setting) {
            Setting::factory()->create($setting);
        }
    }
}
