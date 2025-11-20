<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MidtransSetting;

class MidtransSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default sandbox configuration
        MidtransSetting::updateOrCreate(
            ['id' => 1],
            [
                'server_key' => env('MIDTRANS_SERVER_KEY', ''),
                'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
                'merchant_id' => '',
                'is_production' => false,
                'is_sanitized' => true,
                'is_3ds' => true,
                'is_active' => true,
                'notes' => 'Default sandbox configuration migrated from environment variables. Please update with your actual Midtrans API keys.',
            ]
        );
    }
}