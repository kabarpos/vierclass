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
            // Hapus indeks non-unik untuk menghindari redundansi sebelum menambahkan unique constraint
            try {
                $table->dropIndex('idx_booking_trx_id');
            } catch (\Throwable $e) {
                // Index mungkin belum ada pada beberapa environment; abaikan agar migration tetap lanjut
            }

            // Tambahkan unique constraint pada booking_trx_id untuk menjamin idempotensi transaksi
            $table->unique('booking_trx_id', 'uniq_booking_trx_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus unique constraint
            try {
                $table->dropUnique('uniq_booking_trx_id');
            } catch (\Throwable $e) {
                // Unique key mungkin belum ada; abaikan
            }

            // Tambahkan kembali indeks non-unik jika diperlukan oleh environment yang rollback
            try {
                $table->index('booking_trx_id', 'idx_booking_trx_id');
            } catch (\Throwable $e) {
                // Abaikan jika sudah ada
            }
        });
    }
};

