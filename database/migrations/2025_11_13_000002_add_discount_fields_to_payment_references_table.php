<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_references', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_references', 'discount_id')) {
                $table->unsignedBigInteger('discount_id')->nullable()->after('course_id');
                $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('set null');
            }

            if (!Schema::hasColumn('payment_references', 'discount_amount')) {
                $table->integer('discount_amount')->nullable()->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_references', function (Blueprint $table) {
            if (Schema::hasColumn('payment_references', 'discount_id')) {
                $table->dropForeign(['discount_id']);
                $table->dropColumn('discount_id');
            }
            if (Schema::hasColumn('payment_references', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
