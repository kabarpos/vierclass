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
        Schema::table('users', function (Blueprint $table) {
            // Cek apakah kolom belum ada sebelum menambah
            if (!Schema::hasColumn('users', 'verification_token')) {
                $table->string('verification_token')->nullable()->after('remember_token')->comment('Token verifikasi untuk email dan WhatsApp');
            }
            
            if (!Schema::hasColumn('users', 'whatsapp_verified_at')) {
                $table->timestamp('whatsapp_verified_at')->nullable()->after('email_verified_at')->comment('Waktu verifikasi WhatsApp');
            }
            
            if (!Schema::hasColumn('users', 'is_account_active')) {
                $table->boolean('is_account_active')->default(false)->after('whatsapp_verified_at')->comment('Status akun aktif (email dan WhatsApp terverifikasi)');
            }
        });
        
        // Tambah index setelah kolom dipastikan ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', ['verification_token'])) {
                $table->index(['verification_token']);
            }
            
            if (!Schema::hasIndex('users', ['is_account_active'])) {
                $table->index(['is_account_active']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['verification_token']);
            $table->dropIndex(['is_account_active']);
            $table->dropColumn([
                'verification_token',
                // 'email_verified_at', // jangan hapus karena sudah ada sebelumnya
                'whatsapp_verified_at',
                'is_account_active'
            ]);
        });
    }
};
