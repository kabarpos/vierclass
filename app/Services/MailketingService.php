<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailketingService
{
    protected ?SmtpSetting $smtpSetting;

    public function __construct()
    {
        $this->smtpSetting = SmtpSetting::getActive();
    }

    /**
     * Whether SMTP service is available and configured
     */
    public function isAvailable(): bool
    {
        return $this->smtpSetting && $this->smtpSetting->isConfigured();
    }

    /**
     * Apply active SMTP configuration to Laravel's mail config.
     */
    public function applyMailConfig(): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        // Tentukan jenis encryption secara defensif berdasarkan port jika tidak diisi
        $encryption = $this->smtpSetting->encryption;
        if (empty($encryption)) {
            $port = (int) $this->smtpSetting->port;
            $encryption = match ($port) {
                465 => 'ssl',
                587 => 'tls',
                default => null,
            };
        }

        $cfg = [
            'mail.default' => 'smtp',
            // Pastikan menggunakan transport SMTP secara eksplisit dan abaikan MAIL_URL jika ada
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.host' => $this->smtpSetting->host,
            'mail.mailers.smtp.port' => (int) $this->smtpSetting->port,
            'mail.mailers.smtp.username' => $this->smtpSetting->username,
            'mail.mailers.smtp.password' => $this->smtpSetting->password,
            'mail.mailers.smtp.encryption' => $encryption,
            // Gunakan domain EHLO yang valid agar handshake SMTP stabil
            'mail.mailers.smtp.local_domain' => parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost',
            'mail.from.address' => $this->smtpSetting->from_email,
            'mail.from.name' => $this->smtpSetting->from_name,
        ];

        foreach ($cfg as $key => $value) {
            Config::set($key, $value);
        }
    }

    /**
     * Send a simple test email to ensure configuration works.
     */
    public function sendTest(?string $toEmail = null): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'SMTP belum dikonfigurasi/diaktifkan',
            ];
        }

        $this->applyMailConfig();

        $recipient = $toEmail ?: (Auth::user()->email ?? $this->smtpSetting->from_email);
        try {
            Mail::mailer('smtp')->raw('Tes koneksi SMTP dari sistem ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'), function ($message) use ($recipient) {
                $message->to($recipient);
                $message->subject('Tes SMTP: ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform'));
            });

            return [
                'success' => true,
                'message' => 'Email tes berhasil dikirim ke ' . $recipient,
            ];
        } catch (\Exception $e) {
            Log::warning('SMTP test email failed', [
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Gagal mengirim email tes: ' . $e->getMessage(),
            ];
        }
    }
}
