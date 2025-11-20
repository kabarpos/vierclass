<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WebsiteSetting;

class WebsiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WebsiteSetting::create([
            'site_name' => 'Upversity.id',
            'site_tagline' => 'Platform Pembelajaran Online Terbaik',
            'site_description' => 'Platform pembelajaran online yang menyediakan berbagai kursus dan e-book berkualitas tinggi untuk meningkatkan skill dan pengetahuan Anda.',
            'meta_keywords' => 'lms, e-book, pembelajaran online, kursus, edukasi, skill development',
            'meta_author' => 'Upversity.id Team',
            'default_thumbnail' => null,
            'logo' => null,
            'favicon' => null,
            'head_scripts' => null,
            'body_scripts' => null,
            'google_analytics_id' => null,
            'facebook_pixel_id' => null,
            'footer_text' => 'Platform pembelajaran online terpercaya untuk mengembangkan skill dan pengetahuan Anda.',
            'footer_copyright' => '© 2024 Upversity.id. All rights reserved.',
            'contact_email' => 'marketing@upversity.id',
            'contact_phone' => '+62 812-3456-7890',
            'social_media_links' => json_encode([
                'facebook' => 'https://facebook.com/lmsebook',
                'twitter' => 'https://twitter.com/lmsebook',
                'instagram' => 'https://instagram.com/lmsebook',
                'linkedin' => 'https://linkedin.com/company/lmsebook',
                'youtube' => 'https://youtube.com/@lmsebook'
            ]),
            'maintenance_mode' => false,
            'maintenance_message' => 'Website sedang dalam maintenance. Silakan kembali lagi nanti.',
            'custom_css' => null
        ]);
    }
}
