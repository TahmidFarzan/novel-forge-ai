<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('novels', function (Blueprint $table) {
            $columns = Schema::getColumnListing('novels');

            $removableColumns = array_values(array_intersect($columns, [
                'novel_continuity',
                'is_18_plus',
                'enable_mature_content',
            ]));

            if ($removableColumns) {
                $table->dropColumn($removableColumns);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novels', function (Blueprint $table) {
            $columns = Schema::getColumnListing('novels');

            $missingColumns = array_values(array_diff([
                'novel_continuity',
                'is_18_plus',
                'enable_mature_content',
            ], $columns));

            foreach ($missingColumns as $column) {
                if ($column === 'novel_continuity') {
                    $table->string('novel_continuity', 255);
                } elseif ($column === 'is_18_plus') {
                    $table->boolean('is_18_plus')->default(false);
                } elseif ($column === 'enable_mature_content') {
                    $table->boolean('enable_mature_content')->default(false);
                }
            }
        });
    }
};