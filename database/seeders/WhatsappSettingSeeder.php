<?php

namespace Database\Seeders;

use App\Models\WhatsappSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WhatsappSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WhatsappSetting::create([
            'api_key' => 'test_api_key_12345',
            'base_url' => 'https://api.dripsender.id',
            'is_active' => true,
            'webhook_url' => 'https://lmsebook.test/webhook/whatsapp',
            'additional_settings' => [
                'timeout' => 30,
                'retry_attempts' => 3,
                'debug_mode' => false,
            ],
        ]);
    }
}