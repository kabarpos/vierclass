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
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_key')->nullable()->comment('Dripsender API Key');
            $table->string('base_url')->default('https://api.dripsender.id')->comment('Dripsender Base URL');
            $table->boolean('is_active')->default(false)->comment('Status aktif WhatsApp service');
            $table->text('webhook_url')->nullable()->comment('URL webhook untuk callback');
            $table->json('additional_settings')->nullable()->comment('Pengaturan tambahan dalam format JSON');
            $table->timestamps();
            
            // Index untuk performance
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
