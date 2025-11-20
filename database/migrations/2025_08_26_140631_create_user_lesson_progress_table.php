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
        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_content_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0); // Track time spent on lesson
            $table->timestamps();
            
            // Unique constraint to prevent duplicate progress entries
            $table->unique(['user_id', 'section_content_id'], 'unique_user_lesson_progress');
            
            // Performance indexes
            $table->index(['user_id', 'course_id'], 'idx_user_course_progress');
            $table->index(['course_id', 'is_completed'], 'idx_course_completion');
            $table->index(['user_id', 'is_completed'], 'idx_user_completion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
};
