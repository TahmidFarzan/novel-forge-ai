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
        Schema::create('novel_generator_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_generator_id')->constrained('novel_generators')->cascadeOnDelete();
            $table->foreignId('privous_novel_generator_step_id')->nullable()->constrained('novel_generator_steps')->cascadeOnDelete();
            $table->foreignId('ai_prompt_id')->constrained('ai_prompts')->cascadeOnDelete();
            $table->string('name', 255)->unique();
            $table->json('depends_on_steps')->nullable();
            $table->json('input')->nullable();
            $table->json('outout')->nullable();
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
        Schema::dropIfExists('novel_generator_steps');
    }
};
