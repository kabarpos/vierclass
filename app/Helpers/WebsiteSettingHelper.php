<?php

namespace App\Helpers;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class WebsiteSettingHelper
{
    /**
     * Cache key untuk pengaturan website
     */
    const CACHE_KEY = 'website_settings';

    /**
     * Cache duration dalam menit
     */
    const CACHE_DURATION = 60;

    /**
     * Get all website settings dengan caching
     */
    public static function getSettings()
    {
        // Lindungi dari kegagalan koneksi DB saat bootstrap/package:discover
        try {
            if (Schema::hasTable('website_settings')) {
                return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
                    try {
                        return WebsiteSetting::getInstance();
                    } catch (\Throwable $e) {
                        return new WebsiteSetting();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Jika koneksi DB gagal (mis. service belum hidup), kembalikan objek kosong
            return new WebsiteSetting();
        }

        // Tabel belum ada
        return new WebsiteSetting();
    }

    /**
     * Get specific setting value
     */
    public static function get($key, $default = null)
    {
        $settings = self::getSettings();
        return $settings->$key ?? $default;
    }

    /**
     * Clear cache pengaturan website
     */
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get site name
     */
    public static function getSiteName()
    {
        return self::get('site_name', 'Upversity.id');
    }

    /**
     * Get site tagline/subtitle
     */
    public static function getSiteTagline()
    {
        return self::get('site_tagline', 'Platform Pembelajaran Online');
    }

    /**
     * Get site description
     */
    public static function getSiteDescription()
    {
        return self::get('site_description', 'Platform pembelajaran online terbaik');
    }

    /**
     * Get meta keywords
     */
    public static function getMetaKeywords()
    {
        return self::get('meta_keywords', 'lms, e-book, pembelajaran online');
    }

    /**
     * Get meta author
     */
    public static function getMetaAuthor()
    {
        return self::get('meta_author', 'Upversity.id Team');
    }

    /**
     * Get logo URL
     */
    public static function getLogoUrl()
    {
        $logo = self::get('logo');
        return $logo ? Storage::disk('public')->url($logo) : null;
    }

    /**
     * Get favicon URL
     */
    public static function getFaviconUrl()
    {
        $favicon = self::get('favicon');
        return $favicon ? Storage::disk('public')->url($favicon) : null;
    }

    /**
     * Get default thumbnail URL
     */
    public static function getDefaultThumbnailUrl()
    {
        $thumbnail = self::get('default_thumbnail');
        return $thumbnail ? Storage::disk('public')->url($thumbnail) : null;
    }

    /**
     * Get head scripts
     */
    public static function getHeadScripts()
    {
        return self::get('head_scripts');
    }

    /**
     * Get body scripts
     */
    public static function getBodyScripts()
    {
        return self::get('body_scripts');
    }

    /**
     * Get footer text
     */
    public static function getFooterText()
    {
        return self::get('footer_text');
    }

    /**
     * Get footer copyright
     */
    public static function getFooterCopyright()
    {
        return self::get('footer_copyright', '© ' . date('Y') . ' ' . self::getSiteName() . '. All rights reserved.');
    }

    /**
     * Get contact email
     */
    public static function getContactEmail()
    {
        return self::get('contact_email');
    }

    /**
     * Get contact phone
     */
    public static function getContactPhone()
    {
        return self::get('contact_phone');
    }

    /**
     * Get contact address
     */
    public static function getContactAddress()
    {
        return self::get('contact_address');
    }

    /**
     * Get social media links
     */
    public static function getSocialMediaLinks()
    {
        $links = self::get('social_media_links');
        return is_array($links) ? $links : [];
    }

    /**
     * Check if maintenance mode is active
     */
    public static function isMaintenanceMode()
    {
        return self::get('maintenance_mode', false);
    }

    /**
     * Get maintenance message
     */
    public static function getMaintenanceMessage()
    {
        return self::get('maintenance_message', 'Website sedang dalam maintenance. Silakan coba lagi nanti.');
    }

    /**
     * Get Google Analytics ID
     */
    public static function getGoogleAnalyticsId()
    {
        return self::get('google_analytics_id');
    }

    /**
     * Get Facebook Pixel ID
     */
    public static function getFacebookPixelId()
    {
        return self::get('facebook_pixel_id');
    }

    /**
     * Get custom CSS
     */
    public static function getCustomCss()
    {
        return self::get('custom_css');
    }

    /**
     * Generate complete page title
     */
    public static function getPageTitle($pageTitle = null)
    {
        $siteName = self::getSiteName();
        $tagline = self::getSiteTagline();
        
        if ($pageTitle) {
            return $pageTitle . ' - ' . $siteName;
        }
        
        if ($tagline) {
            return $siteName . ' - ' . $tagline;
        }
        
        return $siteName;
    }

    /**
     * Generate meta tags array
     */
    public static function getMetaTags($pageTitle = null, $pageDescription = null)
    {
        return [
            'title' => self::getPageTitle($pageTitle),
            'description' => $pageDescription ?: self::getSiteDescription(),
            'keywords' => self::getMetaKeywords(),
            'author' => self::getMetaAuthor(),
            'site_name' => self::getSiteName(),
        ];
    }

    /**
     * Generate Google Analytics script
     */
    public static function getGoogleAnalyticsScript()
    {
        $gaId = self::getGoogleAnalyticsId();
        
        if (!$gaId) {
            return '';
        }

        return "
        <!-- Google Analytics -->
        <script async src=\"https://www.googletagmanager.com/gtag/js?id={$gaId}\"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{$gaId}');
        </script>
        ";
    }

    /**
     * Generate Facebook Pixel script
     */
    public static function getFacebookPixelScript()
    {
        $pixelId = self::getFacebookPixelId();
        
        if (!$pixelId) {
            return '';
        }

        return "
        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{$pixelId}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height=\"1\" width=\"1\" style=\"display:none\"
            src=\"https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1\"
        /></noscript>
        ";
    }
}
