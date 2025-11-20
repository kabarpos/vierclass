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
        // Add performance indexes for transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'is_paid', 'ended_at'], 'idx_user_paid_active');
            $table->index('booking_trx_id', 'idx_booking_trx_id');
            $table->index(['is_paid', 'ended_at'], 'idx_paid_active');
        });

        // Add performance indexes for courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->index(['category_id', 'is_popular'], 'idx_category_popular');
            $table->index('slug', 'idx_slug');
            $table->index('is_popular', 'idx_popular');
            $table->index(['category_id', 'created_at'], 'idx_category_created');
        });

        // Add performance indexes for course_students table
        Schema::table('course_students', function (Blueprint $table) {
            $table->index(['user_id', 'course_id', 'is_active'], 'idx_user_course_active');
            $table->index(['course_id', 'is_active'], 'idx_course_active');
        });

        // Add performance indexes for course_sections table
        Schema::table('course_sections', function (Blueprint $table) {
            $table->index(['course_id', 'position'], 'idx_course_position');
        });

        // Add performance indexes for section_contents table
        Schema::table('section_contents', function (Blueprint $table) {
            $table->index('course_section_id', 'idx_section_id');
        });

        // Add performance indexes for course_mentors table
        Schema::table('course_mentors', function (Blueprint $table) {
            $table->index(['course_id', 'is_active'], 'idx_course_mentor_active');
            $table->index(['user_id', 'is_active'], 'idx_user_mentor_active');
        });

        // Add performance indexes for categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug', 'idx_category_slug');
        });

        // Add performance indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('email_verified_at', 'idx_email_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_user_paid_active');
            $table->dropIndex('idx_booking_trx_id');
            $table->dropIndex('idx_paid_active');
        });

        // Drop indexes for courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_category_popular');
            $table->dropIndex('idx_slug');
            $table->dropIndex('idx_popular');
            $table->dropIndex('idx_category_created');
        });

        // Drop indexes for course_students table
        Schema::table('course_students', function (Blueprint $table) {
            $table->dropIndex('idx_user_course_active');
            $table->dropIndex('idx_course_active');
        });

        // Drop indexes for course_sections table
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropIndex('idx_course_position');
        });

        // Drop indexes for section_contents table
        Schema::table('section_contents', function (Blueprint $table) {
            $table->dropIndex('idx_section_id');
        });

        // Drop indexes for course_mentors table
        Schema::table('course_mentors', function (Blueprint $table) {
            $table->dropIndex('idx_course_mentor_active');
            $table->dropIndex('idx_user_mentor_active');
        });

        // Drop indexes for categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_category_slug');
        });

        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_email_verified');
        });
    }
};
