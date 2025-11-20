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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']); // percentage atau fixed amount
            $table->decimal('value', 10, 2); // nilai diskon
            $table->decimal('minimum_amount', 10, 2)->nullable(); // minimum pembelian
            $table->decimal('maximum_discount', 10, 2)->nullable(); // maksimal diskon untuk percentage
            $table->integer('usage_limit')->nullable(); // batas penggunaan
            $table->integer('used_count')->default(0); // jumlah sudah digunakan
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
