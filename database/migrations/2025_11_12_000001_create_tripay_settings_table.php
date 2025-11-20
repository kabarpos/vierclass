<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tripay_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_key');
            $table->string('private_key');
            $table->string('merchant_code');
            $table->boolean('is_production')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tripay_settings');
    }
};

