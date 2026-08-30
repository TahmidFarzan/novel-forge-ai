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
        Schema::create('ai_brain_runners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('url', 255);
            $table->text('brief')->nullable();
            $table->string('slug')->unique();

            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_brain_runners');
    }
};
