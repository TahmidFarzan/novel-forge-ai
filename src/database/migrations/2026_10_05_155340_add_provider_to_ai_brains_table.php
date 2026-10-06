<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_brains', function (Blueprint $table) {
            $table->string('provider', 100)->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('ai_brains', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }
};
