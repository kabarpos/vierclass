<?php

namespace Database\Seeders;

use App\Models\WhatsappMessageTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoursePurchaseTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WhatsappMessageTemplate::updateOrCreate(
            ['type' => WhatsappMessageTemplate::TYPE_COURSE_PURCHASE],
            [
                'name' => 'Pembelian Kursus Individual',
                'type' => WhatsappMessageTemplate::TYPE_COURSE_PURCHASE,
                'subject' => 'Kursus Berhasil Dibeli - {course_name}',
                'message' => "Halo {user_name}! 🎉\n\nSelamat! Pembelian kursus Anda telah berhasil diproses dan Anda sekarang memiliki akses SEUMUR HIDUP ke kursus ini!\n\n📚 Detail Kursus:\n• Nama Kursus: {course_name}\n• Harga: {course_price}\n• ID Transaksi: {transaction_id}\n\n🚀 Mulai belajar sekarang juga!\nAkses kursus: {course_url}\n\n📋 Lihat semua kursus Anda:\n{dashboard_url}\n\n🎯 Tips: Kursus ini milik Anda selamanya, jadi Anda bisa belajar sesuai kecepatan Anda sendiri!\n\nSelamat belajar dan semoga sukses!\n\nTerima kasih telah memilih {app_name}! 🙏",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'course_name' => 'Nama kursus yang dibeli',
                    'course_price' => 'Harga kursus',
                    'transaction_id' => 'ID transaksi',
                    'course_url' => 'Link akses kursus',
                    'dashboard_url' => 'Link dashboard pengguna',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk notifikasi WhatsApp setelah pembelian kursus individual berhasil. Berbeda dengan subscription, ini untuk pembelian per-kursus dengan akses seumur hidup.',
            ]
        );
    }
}
