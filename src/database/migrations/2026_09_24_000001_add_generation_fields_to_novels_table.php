<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_COLUMNS = [
        'current_generator_step_id',
        'generation_error',
        'ai_brain_text_id',
        'generation_started_at',
        'generation_stopped_at',
        'generation_failed_at',
        'completed_generator_step_ids',
    ];

    public function up(): void
    {
        $this->dropLegacyForeignKeys();

        Schema::table('novels', function (Blueprint $table) {
            foreach (self::LEGACY_COLUMNS as $column) {
                if (! Schema::hasColumn('novels', $column)) {
                    continue;
                }

                $table->dropColumn($column);
            }

            if (! Schema::hasColumn('novels', 'ai_brain_id')) {
                $table->foreignId('ai_brain_id')
                    ->nullable()
                    ->constrained('ai_brains')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('novels', 'generation_steps')) {
                $table->jsonb('generation_steps')->nullable();
            }
        });

        if (Schema::hasTable('novel_generation_steps')) {
            Schema::dropIfExists('novel_generation_steps');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('novels', 'generation_steps')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->dropColumn('generation_steps');
            });
        }

        if (Schema::hasColumn('novels', 'ai_brain_id')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->dropConstrainedForeignId('ai_brain_id');
            });
        }
    }

    private function dropLegacyForeignKeys(): void
    {
        $foreignKeys = DB::select('PRAGMA foreign_key_list(novels)');

        $legacyColumns = collect($foreignKeys)
            ->pluck('from')
            ->filter(fn (?string $column) => in_array($column, self::LEGACY_COLUMNS, true))
            ->unique()
            ->values();

        if ($legacyColumns->isEmpty()) {
            return;
        }

        Schema::table('novels', function (Blueprint $table) use ($legacyColumns) {
            $table->dropForeign($legacyColumns->all());
        });
    }
};
