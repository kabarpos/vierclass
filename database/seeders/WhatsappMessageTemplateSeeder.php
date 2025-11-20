<?php

namespace Database\Seeders;

use App\Models\WhatsappMessageTemplate;
use Illuminate\Database\Seeder;

class WhatsappMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Verifikasi Pendaftaran',
                'type' => WhatsappMessageTemplate::TYPE_REGISTRATION_VERIFICATION,
                'subject' => 'Verifikasi Akun {app_name}',
                'message' => "Halo {user_name}! 👋\n\nSelamat datang di {app_name}!\n\nUntuk melengkapi proses pendaftaran, silakan verifikasi akun Anda dengan mengklik link berikut:\n\n🔗 {verification_link}\n\n⚠️ Penting: Jika link verifikasi tidak diklik, akun Anda TIDAK AKAN AKTIF dan tidak bisa mengakses kursus.\n\n📞 Butuh bantuan? Hubungi customer service kami.\n\nTerima kasih! 🙏",
                'variables' => [
                    'user_name' => 'Nama pengguna yang mendaftar',
                    'verification_link' => 'Link verifikasi untuk aktivasi akun',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk mengirim link verifikasi kepada user yang baru mendaftar. Link ini wajib diklik agar akun menjadi aktif.',
            ],
            [
                'name' => 'Penyelesaian Order',
                'type' => WhatsappMessageTemplate::TYPE_ORDER_COMPLETION,
                'subject' => 'Order Berhasil - {order_id}',
                'message' => "Halo {user_name}! 🎉\n\nTerima kasih telah melakukan pemesanan di {app_name}!\n\n📋 Detail Order:\n• ID Order: {order_id}\n• Kursus: {course_name}\n• Total Pembayaran: {total_amount}\n\n💳 Untuk menyelesaikan pembayaran, silakan klik link berikut:\n{payment_link}\n\n⏰ Selesaikan pembayaran dalam 24 jam agar order tidak dibatalkan otomatis.\n\n📞 Ada pertanyaan? Hubungi customer service kami.\n\nTerima kasih! 🙏",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'order_id' => 'ID pesanan',
                    'course_name' => 'Nama kursus yang dipesan',
                    'total_amount' => 'Total pembayaran',
                    'payment_link' => 'Link pembayaran',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk notifikasi setelah user menyelesaikan order, berisi detail pesanan dan link pembayaran.',
            ],
            [
                'name' => 'Pembayaran Diterima',
                'type' => WhatsappMessageTemplate::TYPE_PAYMENT_RECEIVED,
                'subject' => 'Pembayaran Diterima - {order_id}',
                'message' => "Halo {user_name}! ✅\n\nKabar gembira! Pembayaran Anda telah kami terima dan dikonfirmasi.\n\n🎊 Detail Pembayaran:\n• ID Order: {order_id}\n• Kursus: {course_name}\n• Total: {total_amount}\n\n🚀 Sekarang Anda sudah bisa mengakses seluruh materi kursus!\n\nAyo mulai belajar dan raih kesuksesan Anda! 💪\n\n📚 Akses kursus: {app_name}\n\nSelamat belajar! 🎓",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'order_id' => 'ID pesanan',
                    'course_name' => 'Nama kursus',
                    'total_amount' => 'Total pembayaran',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk ucapan terima kasih setelah pembayaran dikonfirmasi oleh admin.',
            ],
            [
                'name' => 'Reset Password',
                'type' => WhatsappMessageTemplate::TYPE_PASSWORD_RESET,
                'subject' => 'Reset Password - {app_name}',
                'message' => "Halo {user_name}! 🔐\n\nAnda telah meminta untuk mereset password akun Anda di {app_name}.\n\n🔗 Klik link berikut untuk mereset password:\n{reset_url}\n\n⚠️ Link ini akan kedaluwarsa dalam {expiry_time}.\n\n🛡️ Jika Anda tidak meminta reset password, abaikan pesan ini dan akun Anda tetap aman.\n\n📞 Butuh bantuan? Hubungi customer service kami.\n\nTerima kasih! 🙏",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'reset_url' => 'Link untuk reset password',
                    'app_name' => 'Nama aplikasi LMS',
                    'expiry_time' => 'Waktu kedaluwarsa link',
                ],
                'is_active' => true,
                'description' => 'Template untuk mengirim link reset password melalui WhatsApp.',
            ],
            [
                'name' => 'Pembelian Kursus Individual',
                'type' => WhatsappMessageTemplate::TYPE_COURSE_PURCHASE,
                'subject' => 'Kursus Berhasil Dibeli - {course_name}',
                'message' => "Halo {user_name}! 🎉\n\nSelamat! Pembelian kursus Anda telah berhasil diproses dan Anda sekarang memiliki akses SEUMUR HIDUP ke kursus ini!\n\n📚 Detail Kursus:\n• Nama Kursus: {course_name}\n• Harga: {course_price}\n• ID Transaksi: {transaction_id}\n\n🚀 Mulai belajar sekarang:\n{course_url}\n\n📊 Dashboard Anda:\n{dashboard_url}\n\n💡 Tips: Manfaatkan fitur progress tracking untuk memantau kemajuan belajar Anda!\n\nSelamat belajar dan raih kesuksesan! 🎓✨",
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
            ],
            [
                'name' => 'Reminder Pembayaran',
                'type' => 'payment_reminder',
                'subject' => 'Reminder Pembayaran - {order_id}',
                'message' => "Halo {user_name}! ⏰\n\nIni adalah pengingat bahwa pembayaran untuk order Anda belum diselesaikan.\n\n📋 Detail Order:\n• ID Order: {order_id}\n• Kursus: {course_name}\n• Total: {total_amount}\n• Batas Waktu: {expiry_time}\n\n💳 Selesaikan pembayaran sekarang:\n{payment_link}\n\n⚠️ Order akan dibatalkan otomatis jika tidak dibayar sebelum batas waktu.\n\n📞 Butuh bantuan? Hubungi customer service kami.\n\nTerima kasih! 🙏",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'order_id' => 'ID pesanan',
                    'course_name' => 'Nama kursus',
                    'total_amount' => 'Total pembayaran',
                    'payment_link' => 'Link pembayaran',
                    'expiry_time' => 'Batas waktu pembayaran',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk mengingatkan user yang belum menyelesaikan pembayaran.',
            ],
            [
                'name' => 'Order Dibatalkan',
                'type' => 'order_cancelled',
                'subject' => 'Order Dibatalkan - {order_id}',
                'message' => "Halo {user_name}! ❌\n\nOrder Anda telah dibatalkan karena pembayaran tidak diselesaikan dalam batas waktu yang ditentukan.\n\n📋 Detail Order yang Dibatalkan:\n• ID Order: {order_id}\n• Kursus: {course_name}\n• Total: {total_amount}\n\n🔄 Jangan khawatir! Anda masih bisa melakukan pemesanan ulang kapan saja.\n\n🛒 Pesan lagi sekarang:\n{course_url}\n\n📞 Ada pertanyaan? Hubungi customer service kami.\n\nTerima kasih! 🙏",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'order_id' => 'ID pesanan yang dibatalkan',
                    'course_name' => 'Nama kursus',
                    'total_amount' => 'Total pembayaran',
                    'course_url' => 'Link untuk pesan ulang',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk notifikasi ketika order dibatalkan karena tidak dibayar.',
            ],
            [
                'name' => 'Selamat Datang User Baru',
                'type' => 'welcome_new_user',
                'subject' => 'Selamat Datang di {app_name}!',
                'message' => "Halo {user_name}! 🎉\n\nSelamat datang di {app_name}! Akun Anda telah berhasil diverifikasi dan siap digunakan.\n\n🚀 Apa yang bisa Anda lakukan:\n• Jelajahi ribuan kursus berkualitas\n• Akses materi pembelajaran interaktif\n• Dapatkan sertifikat setelah menyelesaikan kursus\n• Bergabung dengan komunitas pembelajar\n\n📚 Mulai eksplorasi:\n{dashboard_url}\n\n🎁 Bonus: Dapatkan diskon khusus untuk pembelian kursus pertama Anda!\n\n📞 Butuh bantuan? Tim support kami siap membantu 24/7.\n\nSelamat belajar dan raih impian Anda! 🌟",
                'variables' => [
                    'user_name' => 'Nama pengguna baru',
                    'dashboard_url' => 'Link dashboard pengguna',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk menyambut user baru setelah verifikasi akun berhasil.',
            ],
            [
                'name' => 'Progress Kursus',
                'type' => 'course_progress',
                'subject' => 'Progress Belajar - {course_name}',
                'message' => "Halo {user_name}! 📈\n\nKami melihat progress belajar Anda di kursus \"{course_name}\" sangat bagus!\n\n🎯 Progress Anda:\n• Selesai: {completed_lessons} dari {total_lessons} pelajaran\n• Persentase: {progress_percentage}%\n• Estimasi selesai: {estimated_completion}\n\n💪 Tetap semangat! Anda sudah sangat dekat dengan menyelesaikan kursus ini.\n\n📚 Lanjutkan belajar:\n{course_url}\n\n🏆 Setelah selesai, Anda akan mendapatkan sertifikat yang bisa digunakan untuk meningkatkan karir!\n\nSemangat belajar! 🌟",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'course_name' => 'Nama kursus',
                    'completed_lessons' => 'Jumlah pelajaran yang selesai',
                    'total_lessons' => 'Total pelajaran dalam kursus',
                    'progress_percentage' => 'Persentase progress',
                    'estimated_completion' => 'Estimasi waktu selesai',
                    'course_url' => 'Link akses kursus',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk memberikan update progress belajar kepada user.',
            ],
            [
                'name' => 'Kursus Selesai',
                'type' => 'course_completed',
                'subject' => 'Selamat! Kursus Selesai - {course_name}',
                'message' => "Selamat {user_name}! 🎊🎉\n\nAnda telah berhasil menyelesaikan kursus \"{course_name}\"!\n\n🏆 Pencapaian Anda:\n• Total pelajaran diselesaikan: {total_lessons}\n• Waktu belajar: {study_duration}\n• Skor rata-rata: {average_score}%\n\n📜 Sertifikat Anda sudah siap!\nDownload di: {certificate_url}\n\n🚀 Langkah selanjutnya:\n• Bagikan pencapaian di LinkedIn\n• Jelajahi kursus lanjutan\n• Bergabung dengan komunitas alumni\n\n🎁 Bonus: Dapatkan diskon 20% untuk kursus berikutnya!\n\nTerima kasih telah belajar bersama kami! 🌟",
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'course_name' => 'Nama kursus yang diselesaikan',
                    'total_lessons' => 'Total pelajaran',
                    'study_duration' => 'Durasi belajar',
                    'average_score' => 'Skor rata-rata',
                    'certificate_url' => 'Link download sertifikat',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template untuk memberikan selamat kepada user yang telah menyelesaikan kursus.',
            ],
        ];

        foreach ($templates as $template) {
            WhatsappMessageTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }
    }
}