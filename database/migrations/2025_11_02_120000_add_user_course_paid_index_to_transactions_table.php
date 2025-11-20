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
        Schema::table('transactions', function (Blueprint $table) {
            // Index komposit untuk mempercepat pengecekan pembelian kursus oleh user
            if (!app()->runningInConsole()) {
                // no-op
            }
            $table->index(['user_id', 'course_id', 'is_paid'], 'idx_user_course_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_user_course_paid');
        });
    }
};

