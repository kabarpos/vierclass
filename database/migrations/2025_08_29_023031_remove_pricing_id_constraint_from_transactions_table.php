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
            // Drop foreign key constraint if it exists
            $table->dropForeign(['pricing_id']);
            // Drop the pricing_id column if it exists
            $table->dropColumn('pricing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to add pricing_id back as it's no longer used
    }
};
