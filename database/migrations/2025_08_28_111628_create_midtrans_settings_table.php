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
        Schema::create('midtrans_settings', function (Blueprint $table) {
            $table->id();
            $table->string('server_key')->nullable()->comment('Midtrans Server Key');
            $table->string('client_key')->nullable()->comment('Midtrans Client Key');
            $table->string('merchant_id')->nullable()->comment('Midtrans Merchant ID');
            $table->boolean('is_production')->default(false)->comment('Production mode flag');
            $table->boolean('is_sanitized')->default(true)->comment('Input sanitization flag');
            $table->boolean('is_3ds')->default(true)->comment('3D Secure flag');
            $table->boolean('is_active')->default(true)->comment('Active configuration flag');
            $table->text('notes')->nullable()->comment('Admin notes about configuration');
            $table->timestamps();
            
            // Add index for performance
            $table->index(['is_active', 'is_production']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midtrans_settings');
    }
};
