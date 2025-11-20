<?php

namespace Database\Seeders;

use App\Models\EmailMessageTemplate;
use Illuminate\Database\Seeder;

class EmailMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Verifikasi Pendaftaran Email',
                'type' => EmailMessageTemplate::TYPE_REGISTRATION_VERIFICATION,
                'subject' => 'Verifikasi Akun {app_name} - Aktivasi Diperlukan',
                'message' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2563eb; margin: 0; font-size: 28px;">Selamat Datang di {app_name}!</h1>
        </div>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 20px;">
            Halo <strong>{user_name}</strong>! 👋
        </p>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
            Terima kasih telah mendaftar di <strong>{app_name}</strong>. Untuk melengkapi proses pendaftaran dan mengaktifkan akun Anda, silakan klik tombol verifikasi di bawah ini:
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{verification_link}" style="display: inline-block; background-color: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                🔗 Verifikasi Akun Sekarang
            </a>
        </div>
        
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 25px 0; border-radius: 4px;">
            <p style="margin: 0; color: #92400e; font-size: 14px;">
                <strong>⚠️ Penting:</strong> Jika link verifikasi tidak diklik dalam 24 jam, akun Anda TIDAK AKAN AKTIF dan tidak dapat digunakan untuk mengakses kursus.
            </p>
        </div>
        
        <p style="font-size: 14px; line-height: 1.6; color: #6b7280; margin-bottom: 10px;">
            Jika tombol di atas tidak berfungsi, Anda dapat menyalin dan menempelkan link berikut ke browser Anda:
        </p>
        <p style="font-size: 12px; color: #6b7280; word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 4px;">
            {verification_link}
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
            Email ini dikirim secara otomatis oleh sistem {app_name}. Jika Anda tidak mendaftar, abaikan email ini.
        </p>
    </div>
</div>',
                'variables' => [
                    'user_name' => 'Nama pengguna yang mendaftar',
                    'verification_link' => 'Link verifikasi untuk aktivasi akun',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template email untuk mengirim link verifikasi kepada user yang baru mendaftar. Link ini wajib diklik agar akun menjadi aktif.',
            ],
            [
                'name' => 'Reset Password Email',
                'type' => EmailMessageTemplate::TYPE_PASSWORD_RESET,
                'subject' => 'Reset Password Akun {app_name}',
                'message' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #dc2626; margin: 0; font-size: 28px;">🔐 Reset Password</h1>
        </div>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 20px;">
            Halo <strong>{user_name}</strong>!
        </p>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
            Anda telah meminta untuk mereset password akun Anda di <strong>{app_name}</strong>. Klik tombol di bawah ini untuk membuat password baru:
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{reset_url}" style="display: inline-block; background-color: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                Reset Password Sekarang
            </a>
        </div>
        
        <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 25px 0; border-radius: 4px;">
            <p style="margin: 0; color: #991b1b; font-size: 14px;">
                <strong>⚠️ Penting:</strong> Link ini akan kedaluwarsa dalam <strong>{expiry_time}</strong>. Jika Anda tidak meminta reset password, abaikan email ini.
            </p>
        </div>
        
        <p style="font-size: 14px; line-height: 1.6; color: #6b7280; margin-bottom: 10px;">
            Jika tombol di atas tidak berfungsi, Anda dapat menyalin dan menempelkan link berikut ke browser Anda:
        </p>
        <p style="font-size: 12px; color: #6b7280; word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 4px;">
            {reset_url}
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
            Untuk keamanan akun Anda, jangan bagikan link ini kepada siapa pun.
        </p>
    </div>
</div>',
                'variables' => [
                    'user_name' => 'Nama pengguna',
                    'reset_url' => 'Link untuk reset password',
                    'app_name' => 'Nama aplikasi LMS',
                    'expiry_time' => 'Waktu kedaluwarsa link',
                ],
                'is_active' => true,
                'description' => 'Template email untuk mengirim link reset password.',
            ],
            [
                'name' => 'Selamat Datang User Baru',
                'type' => EmailMessageTemplate::TYPE_WELCOME_NEW_USER,
                'subject' => 'Selamat Datang di {app_name} - Akun Anda Sudah Aktif!',
                'message' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #059669; margin: 0; font-size: 28px;">🎉 Selamat Datang!</h1>
        </div>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 20px;">
            Halo <strong>{user_name}</strong>!
        </p>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
            Selamat datang di <strong>{app_name}</strong>! Akun Anda telah berhasil diverifikasi dan siap digunakan.
        </p>
        
        <div style="background-color: #ecfdf5; border-left: 4px solid #059669; padding: 20px; margin: 25px 0; border-radius: 4px;">
            <h3 style="color: #065f46; margin: 0 0 15px 0; font-size: 18px;">🚀 Apa yang bisa Anda lakukan:</h3>
            <ul style="color: #065f46; margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 8px;">Jelajahi ribuan kursus berkualitas</li>
                <li style="margin-bottom: 8px;">Akses materi pembelajaran interaktif</li>
                <li style="margin-bottom: 8px;">Dapatkan sertifikat setelah menyelesaikan kursus</li>
                <li style="margin-bottom: 8px;">Bergabung dengan komunitas pembelajar</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{dashboard_url}" style="display: inline-block; background-color: #059669; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                Mulai Belajar Sekarang
            </a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
            Terima kasih telah bergabung dengan {app_name}. Mari mulai perjalanan pembelajaran Anda!
        </p>
    </div>
</div>',
                'variables' => [
                    'user_name' => 'Nama pengguna baru',
                    'dashboard_url' => 'Link dashboard pengguna',
                    'app_name' => 'Nama aplikasi LMS',
                ],
                'is_active' => true,
                'description' => 'Template email untuk menyambut user baru setelah verifikasi akun berhasil.',
            ],
            [
                'name' => 'Pembelian Kursus Berhasil',
                'type' => EmailMessageTemplate::TYPE_COURSE_PURCHASE,
                'subject' => 'Pembelian Berhasil - {course_name}',
                'message' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2563eb; margin: 0; font-size: 28px;">🎉 Pembelian Berhasil!</h1>
        </div>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 20px;">
            Halo <strong>{user_name}</strong>!
        </p>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
            Selamat! Pembelian kursus Anda telah berhasil diproses dan Anda sekarang memiliki akses <strong>SEUMUR HIDUP</strong> ke kursus ini!
        </p>
        
        <div style="background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 20px; margin: 25px 0; border-radius: 4px;">
            <h3 style="color: #1e40af; margin: 0 0 15px 0; font-size: 18px;">📚 Detail Kursus:</h3>
            <ul style="color: #1e40af; margin: 0; padding-left: 20px; list-style: none;">
                <li style="margin-bottom: 8px;"><strong>• Nama Kursus:</strong> {course_name}</li>
                <li style="margin-bottom: 8px;"><strong>• Harga:</strong> {course_price}</li>
                <li style="margin-bottom: 8px;"><strong>• ID Transaksi:</strong> {transaction_id}</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{course_url}" style="display: inline-block; background-color: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; margin-right: 10px;">
                🚀 Mulai Belajar
            </a>
            <a href="{dashboard_url}" style="display: inline-block; background-color: #6b7280; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                📊 Dashboard
            </a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
            Terima kasih telah mempercayai {app_name} untuk perjalanan pembelajaran Anda!
        </p>
    </div>
</div>',
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
                'description' => 'Template email untuk notifikasi setelah pembelian kursus individual berhasil.',
            ],
            [
                'name' => 'Kursus Selesai',
                'type' => EmailMessageTemplate::TYPE_COURSE_COMPLETION,
                'subject' => 'Selamat! Anda Telah Menyelesaikan {course_name}',
                'message' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #059669; margin: 0; font-size: 28px;">🎊 Selamat!</h1>
        </div>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 20px;">
            Selamat <strong>{user_name}</strong>!
        </p>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
            Anda telah berhasil menyelesaikan kursus <strong>"{course_name}"</strong>!
        </p>
        
        <div style="background-color: #ecfdf5; border-left: 4px solid #059669; padding: 20px; margin: 25px 0; border-radius: 4px;">
            <h3 style="color: #065f46; margin: 0 0 15px 0; font-size: 18px;">🏆 Pencapaian Anda:</h3>
            <ul style="color: #065f46; margin: 0; padding-left: 20px; list-style: none;">
                <li style="margin-bottom: 8px;"><strong>• Total pelajaran diselesaikan:</strong> {total_lessons}</li>
                <li style="margin-bottom: 8px;"><strong>• Waktu belajar:</strong> {study_duration}</li>
                <li style="margin-bottom: 8px;"><strong>• Skor rata-rata:</strong> {average_score}%</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{certificate_url}" style="display: inline-block; background-color: #059669; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                📜 Download Sertifikat
            </a>
        </div>
        
        <p style="font-size: 16px; line-height: 1.6; color: #374151; text-align: center; margin: 25px 0;">
            Sertifikat Anda sudah siap untuk diunduh! Bagikan pencapaian ini di LinkedIn atau media sosial lainnya.
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
            Terima kasih telah menyelesaikan kursus di {app_name}. Terus semangat belajar!
        </p>
    </div>
</div>',
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
                'description' => 'Template email untuk memberikan selamat kepada user yang telah menyelesaikan kursus.',
            ],
        ];

        foreach ($templates as $template) {
            EmailMessageTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }
    }
}