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
        Schema::create('kdp_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->text('description')->nullable();
            $table->string('binding_type', 100)->nullable();
            $table->string('page_size', 100)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('top_margin', 6, 2)->nullable();
            $table->decimal('bottom_margin', 6, 2)->nullable();
            $table->decimal('outside_margin', 6, 2)->nullable();
            $table->decimal('gutter', 6, 2)->nullable();
            $table->string('bleed', 50)->nullable();
            $table->string('interior_type', 100)->nullable();
            $table->string('paper_color', 100)->nullable();
            $table->string('trim_size', 100)->nullable();
            $table->string('font_settings', 255)->nullable();
            $table->integer('minimum_page_count')->nullable();
            $table->integer('maximum_page_count')->nullable();
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
        Schema::dropIfExists('kdp_layouts');
    }
};
