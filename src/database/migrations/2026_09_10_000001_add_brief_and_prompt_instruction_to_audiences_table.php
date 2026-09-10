<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audiences', function (Blueprint $table) {
            $table->text('brief')->nullable()->after('slug');
            $table->longText('prompt_instruction')->nullable()->after('brief');
        });
    }

    public function down(): void
    {
        Schema::table('audiences', function (Blueprint $table) {
            $table->dropColumn(['brief', 'prompt_instruction']);
        });
    }
};
