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
        Schema::table('media', function (Blueprint $table) {

            $table->dropMorphs('model');

            $table->string('slug')
                ->nullable()
                ->unique()
                ->after('id');

            $table->nullableMorphs('model');

            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {

            $table->dropForeign(['created_by_id']);

            $table->dropColumn([
                'slug',
                'created_by_id',
            ]);

            $table->dropMorphs('model');

            $table->morphs('model');
        });
    }
};
