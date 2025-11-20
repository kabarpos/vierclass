<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappMessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'message',
        'variables',
        'is_active',
        'description',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // Template types constants
    const TYPE_REGISTRATION_VERIFICATION = 'registration_verification';
    const TYPE_ORDER_COMPLETION = 'order_completion';
    const TYPE_PAYMENT_RECEIVED = 'payment_received';
    const TYPE_COURSE_PURCHASE = 'course_purchase'; // New type for individual course purchases
    const TYPE_PASSWORD_RESET = 'password_reset';

    /**
     * Get template by type
     */
    public static function getByType(string $type)
    {
        return static::where('type', $type)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Replace variables in message with actual values
     */
    public function parseMessage(array $data = []): string
    {
        $message = $this->message;
        
        // Replace variables with actual data
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }

    /**
     * Get available template types
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_REGISTRATION_VERIFICATION => 'Verifikasi Pendaftaran',
            self::TYPE_ORDER_COMPLETION => 'Penyelesaian Order',
            self::TYPE_PAYMENT_RECEIVED => 'Pembayaran Diterima',
            self::TYPE_COURSE_PURCHASE => 'Pembelian Kursus Individual',
            self::TYPE_PASSWORD_RESET => 'Reset Password',
        ];
    }

    /**
     * Get default variables for each template type
     */
    public static function getDefaultVariables(string $type): array
    {
        $variables = [
            self::TYPE_REGISTRATION_VERIFICATION => [
                'user_name' => 'Nama pengguna',
                'verification_link' => 'Link verifikasi',
                'app_name' => 'Nama aplikasi',
            ],
            self::TYPE_ORDER_COMPLETION => [
                'user_name' => 'Nama pengguna',
                'order_id' => 'ID Pesanan',
                'course_name' => 'Nama Kursus',
                'total_amount' => 'Total Pembayaran',
                'payment_link' => 'Link Pembayaran',
                'app_name' => 'Nama aplikasi',
            ],
            self::TYPE_PAYMENT_RECEIVED => [
                'user_name' => 'Nama pengguna',
                'order_id' => 'ID Pesanan',
                'course_name' => 'Nama Kursus',
                'total_amount' => 'Total Pembayaran',
                'app_name' => 'Nama aplikasi',
            ],
            self::TYPE_COURSE_PURCHASE => [
                'user_name' => 'Nama pengguna',
                'course_name' => 'Nama Kursus',
                'course_price' => 'Harga Kursus',
                'transaction_id' => 'ID Transaksi',
                'course_url' => 'Link Akses Kursus',
                'dashboard_url' => 'Link Dashboard',
                'app_name' => 'Nama aplikasi',
            ],
            self::TYPE_PASSWORD_RESET => [
                'user_name' => 'Nama pengguna',
                'reset_url' => 'Link reset password',
                'app_name' => 'Nama aplikasi',
                'expiry_time' => 'Waktu kedaluwarsa',
            ],
        ];

        return $variables[$type] ?? [];
    }

    /**
     * Get system variables for template type (alias for getDefaultVariables)
     */
    public static function getSystemVariables(string $type): array
    {
        return static::getDefaultVariables($type);
    }
}
