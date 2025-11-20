<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('website_settings')) {
            $oldNames = [
                'LMS E-Book',
                'LMS E-Book Platform',
                'LMS-Ebook',
                'LMS-Ebook Platform',
            ];

            // Update nilai site_name lama ke brand baru
            DB::table('website_settings')
                ->whereIn('site_name', $oldNames)
                ->orWhereNull('site_name')
                ->update(['site_name' => 'Upversity.id']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('website_settings')) {
            // Kembalikan ke nilai lama hanya jika saat ini Upversity.id
            DB::table('website_settings')
                ->where('site_name', 'Upversity.id')
                ->update(['site_name' => 'LMS E-Book']);
        }
    }
};

