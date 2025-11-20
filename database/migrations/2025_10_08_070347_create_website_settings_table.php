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
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            
            // SEO Settings
            $table->string('site_name')->default('LMS E-Book');
            $table->string('site_tagline')->nullable();
            $table->text('site_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_author')->nullable();
            
            // Media Settings
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('default_thumbnail')->nullable();
            
            // Scripts Settings
            $table->longText('head_scripts')->nullable();
            $table->longText('body_scripts')->nullable();
            
            // Footer Settings
            $table->text('footer_text')->nullable();
            $table->text('footer_copyright')->nullable();
            
            // Additional Settings
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('social_media_links')->nullable(); // JSON format
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            
            // Analytics & Tracking
            $table->string('google_analytics_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->text('custom_css')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};
