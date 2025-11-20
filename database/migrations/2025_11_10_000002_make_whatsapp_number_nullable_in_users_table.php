<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'whatsapp_number')) {
            return; // Kolom tidak ada, tidak perlu perubahan
        }

        $driver = config('database.default');

        try {
            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    DB::statement('ALTER TABLE `users` MODIFY `whatsapp_number` VARCHAR(255) NULL');
                    break;
                case 'pgsql':
                    DB::statement('ALTER TABLE users ALTER COLUMN whatsapp_number DROP NOT NULL');
                    break;
                case 'sqlite':
                    // SQLite tidak mendukung ALTER DROP NOT NULL dengan mudah; abaikan di lokal
                    break;
                case 'sqlsrv':
                    DB::statement('ALTER TABLE [users] ALTER COLUMN [whatsapp_number] NVARCHAR(255) NULL');
                    break;
                default:
                    // Fallback: coba perintah MySQL
                    DB::statement('ALTER TABLE `users` MODIFY `whatsapp_number` VARCHAR(255) NULL');
            }
        } catch (\Throwable $e) {
            // Logging agar mudah ditrace jika gagal
            \Log::warning('Gagal mengubah kolom whatsapp_number menjadi nullable', [
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'whatsapp_number')) {
            return;
        }

        $driver = config('database.default');

        try {
            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    DB::statement('ALTER TABLE `users` MODIFY `whatsapp_number` VARCHAR(255) NOT NULL');
                    break;
                case 'pgsql':
                    DB::statement('ALTER TABLE users ALTER COLUMN whatsapp_number SET NOT NULL');
                    break;
                case 'sqlite':
                    // Abaikan
                    break;
                case 'sqlsrv':
                    DB::statement('ALTER TABLE [users] ALTER COLUMN [whatsapp_number] NVARCHAR(255) NOT NULL');
                    break;
                default:
                    DB::statement('ALTER TABLE `users` MODIFY `whatsapp_number` VARCHAR(255) NOT NULL');
            }
        } catch (\Throwable $e) {
            \Log::warning('Gagal mengembalikan kolom whatsapp_number ke NOT NULL', [
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
};

