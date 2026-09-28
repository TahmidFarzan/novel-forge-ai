<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const RENAMES = [
        'Story Package Generator' => 'Foundation',
        'Plan Package Generator' => 'Plan Chapter',
        'Chapter Content Generator' => 'Chapter Content',
    ];

    public function up(): void
    {
        $this->applyRenames(self::RENAMES);
    }

    public function down(): void
    {
        $this->applyRenames(array_flip(self::RENAMES));
    }

    private function applyRenames(array $renames): void
    {
        foreach ($renames as $from => $to) {
            if (DB::table('ai_prompts')->where('name', $to)->exists()) {
                continue;
            }

            DB::table('ai_prompts')
                ->where('name', $from)
                ->update([
                    'name'       => $to,
                    'code'       => Str::studly($to),
                    'slug'       => Str::slug($to),
                    'updated_at' => now(),
                ]);

            DB::table('novel_generator_steps')
                ->where('name', $from)
                ->update([
                    'name'       => $to,
                    'slug'       => Str::slug($to),
                    'updated_at' => now(),
                ]);
        }
    }
};
