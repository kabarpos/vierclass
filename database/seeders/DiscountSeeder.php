<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'name' => 'Diskon Tahun Baru 2025',
                'code' => 'NEWYEAR2025',
                'description' => 'Diskon spesial untuk menyambut tahun baru 2025',
                'type' => 'percentage',
                'value' => 25.00,
                'minimum_amount' => 100000,
                'maximum_discount' => 50000,
                'usage_limit' => 100,
                'used_count' => 0,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(30),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Flash Sale',
                'code' => 'FLASH50',
                'description' => 'Flash sale diskon 50% untuk pembelian hari ini',
                'type' => 'percentage',
                'value' => 50.00,
                'minimum_amount' => 200000,
                'maximum_discount' => 100000,
                'usage_limit' => 50,
                'used_count' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addHours(24),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Tetap 25K',
                'code' => 'SAVE25K',
                'description' => 'Hemat Rp 25.000 untuk setiap pembelian',
                'type' => 'fixed',
                'value' => 25000,
                'minimum_amount' => 75000,
                'maximum_discount' => null,
                'usage_limit' => 200,
                'used_count' => 0,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(60),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Student 15%',
                'code' => 'STUDENT15',
                'description' => 'Diskon khusus untuk pelajar dan mahasiswa',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_amount' => 50000,
                'maximum_discount' => 30000,
                'usage_limit' => null, // unlimited
                'used_count' => 0,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(90),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Expired (Testing)',
                'code' => 'EXPIRED',
                'description' => 'Diskon yang sudah expired untuk testing',
                'type' => 'percentage',
                'value' => 20.00,
                'minimum_amount' => 100000,
                'maximum_discount' => 40000,
                'usage_limit' => 10,
                'used_count' => 0,
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->subDays(5),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Inactive (Testing)',
                'code' => 'INACTIVE',
                'description' => 'Diskon yang tidak aktif untuk testing',
                'type' => 'percentage',
                'value' => 30.00,
                'minimum_amount' => 100000,
                'maximum_discount' => 60000,
                'usage_limit' => 50,
                'used_count' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(30),
                'is_active' => false,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }
    }
}
