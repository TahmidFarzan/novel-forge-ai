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
        Schema::create('novel_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
            $table->string('no', 255);
            $table->string('title', 255);

            $table->integer('word_count')->nullable();

            $table->text('summery')->nullable();
            $table->longText('content')->nullable();

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
        Schema::dropIfExists('novel_chapters');
    }
};
