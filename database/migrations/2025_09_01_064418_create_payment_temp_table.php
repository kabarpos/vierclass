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
        Schema::create('payment_temp', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique(); // Midtrans order ID
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->decimal('sub_total_amount', 15, 2);
            $table->decimal('admin_fee_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->decimal('grand_total_amount', 15, 2);
            $table->string('snap_token')->nullable();
            $table->json('discount_data')->nullable(); // Store full discount info
            $table->timestamp('expires_at')->nullable(); // Auto cleanup old records
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('set null');
            
            $table->index(['order_id', 'user_id']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_temp');
    }
};
