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
        Schema::create('whatsapp_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama template');
            $table->string('type')->comment('Tipe template: registration_verification, order_completion, payment_received');
            $table->string('subject')->nullable()->comment('Subject template');
            $table->text('message')->comment('Isi pesan template');
            $table->json('variables')->nullable()->comment('Variabel yang tersedia dalam template');
            $table->boolean('is_active')->default(true)->comment('Status aktif template');
            $table->text('description')->nullable()->comment('Deskripsi template');
            $table->timestamps();
            
            // Index untuk performance
            $table->index(['type', 'is_active']);
            $table->unique(['type']); // Setiap tipe hanya boleh ada satu template aktif
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_templates');
    }
};
