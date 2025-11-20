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
        // Add price column to courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->after('is_popular');
        });
        
        // Modify transactions table to support course purchases
        Schema::table('transactions', function (Blueprint $table) {
            // Add course_id foreign key
            $table->foreignId('course_id')->nullable()->after('pricing_id')->constrained()->onDelete('cascade');
            
            // Make pricing_id nullable (for backward compatibility)
            $table->unsignedBigInteger('pricing_id')->nullable()->change();
            
            // Add index for better performance
            $table->index(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove course_id from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropIndex(['user_id', 'course_id']);
            $table->dropColumn('course_id');
            
            // Make pricing_id required again
            $table->unsignedBigInteger('pricing_id')->nullable(false)->change();
        });
        
        // Remove price column from courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
