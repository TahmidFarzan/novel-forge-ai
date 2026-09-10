<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novel_generators', function (Blueprint $table) {
            $table->foreignId('audience_id')->nullable()->after('slug')->constrained('audiences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('novel_generators', function (Blueprint $table) {
            $table->dropForeign(['audience_id']);
            $table->dropColumn('audience_id');
        });
    }
};
