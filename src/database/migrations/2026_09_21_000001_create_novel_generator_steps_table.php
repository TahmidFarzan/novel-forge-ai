<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novel_generator_steps', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('slug')->unique();
            $table->json('depend_on_step_ids')->nullable();

            $table->foreignId('previous_step_id')
                ->nullable()
                ->constrained('novel_generator_steps')
                ->nullOnDelete();

            $table->foreignId('next_step_id')
                ->nullable()
                ->constrained('novel_generator_steps')
                ->nullOnDelete();

            $table->foreignId('ai_prompt_id')
                ->nullable()
                ->constrained('ai_prompts')
                ->nullOnDelete();

            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novel_generator_steps');
    }
};
