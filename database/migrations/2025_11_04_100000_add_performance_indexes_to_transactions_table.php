<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Mendukung filter rentang tanggal pada revenue (started_at) dan status pembayaran
            $table->index(['is_paid', 'started_at'], 'idx_paid_started');

            // Mendukung agregasi dan withCount per course pada transaksi yang sudah dibayar
            $table->index(['course_id', 'is_paid'], 'idx_course_paid');

            // Mendukung sort/filter spesifik hanya pada kolom tanggal mulai
            $table->index('started_at', 'idx_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_paid_started');
            $table->dropIndex('idx_course_paid');
            $table->dropIndex('idx_started_at');
        });
    }
};

