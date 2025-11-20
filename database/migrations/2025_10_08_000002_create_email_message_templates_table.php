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
        Schema::create('email_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama template');
            $table->string('type')->comment('Tipe template');
            $table->string('subject')->nullable()->comment('Subject template');
            $table->longText('message')->comment('Isi pesan (HTML/Text)');
            $table->json('variables')->nullable()->comment('Variabel tersedia');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->text('description')->nullable()->comment('Deskripsi template');
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->unique(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_message_templates');
    }
};