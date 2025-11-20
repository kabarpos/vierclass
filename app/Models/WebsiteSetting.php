<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_tagline',
        'site_description',
        'meta_keywords',
        'meta_author',
        'logo',
        'favicon',
        'default_thumbnail',
        'head_scripts',
        'body_scripts',
        'footer_text',
        'footer_copyright',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_media_links',
        'maintenance_mode',
        'maintenance_message',
        'google_analytics_id',
        'facebook_pixel_id',
        'custom_css',
        'default_payment_gateway',
    ];

    protected $casts = [
        'social_media_links' => 'array',
        'maintenance_mode' => 'boolean',
    ];

    /**
     * Get the singleton instance of website settings
     */
    public static function getInstance()
    {
        return static::firstOrCreate([]);
    }

    /**
     * Get a specific setting value
     */
    public static function get($key, $default = null)
    {
        $settings = static::getInstance();
        return $settings->$key ?? $default;
    }

    /**
     * Set a specific setting value
     */
    public static function set($key, $value)
    {
        $settings = static::getInstance();
        $settings->$key = $value;
        $settings->save();
        return $settings;
    }
}
