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
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host')->nullable()->comment('SMTP host');
            $table->unsignedInteger('port')->default(587)->comment('SMTP port');
            $table->string('username')->nullable()->comment('SMTP username');
            $table->string('password')->nullable()->comment('SMTP password');
            $table->string('encryption')->nullable()->default('tls')->comment('Encryption: tls/ssl');
            $table->string('from_name')->nullable()->comment('Global email from name');
            $table->string('from_email')->nullable()->comment('Global email from address');
            $table->boolean('is_active')->default(false)->comment('Active SMTP configuration');
            // Optional Mailketing API fields, for future use
            $table->string('api_endpoint')->nullable()->comment('Mailketing API endpoint');
            $table->string('api_login')->nullable()->comment('Mailketing API login');
            $table->string('api_token')->nullable()->comment('Mailketing API token');
            $table->json('additional_settings')->nullable()->comment('Additional settings in JSON');
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};