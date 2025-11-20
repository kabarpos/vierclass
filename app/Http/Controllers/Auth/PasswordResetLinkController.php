<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Models\User;
use App\Services\WhatsappNotificationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Terapkan konfigurasi SMTP aktif sebelum mengirim link reset
        try {
            app(\App\Services\MailketingService::class)->applyMailConfig();
        } catch (\Throwable $cfgEx) {
            \Log::warning('Failed to apply SMTP config before sending password reset link', [
                'email' => $request->email,
                'error' => $cfgEx->getMessage(),
            ]);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }

    /**
     * Handle password reset via WhatsApp
     */
    public function sendWhatsAppReset(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan dalam sistem.']);
        }

        if (!$user->whatsapp_number) {
            return back()->withErrors(['email' => 'Nomor WhatsApp tidak terdaftar untuk akun ini. Silakan gunakan reset via email.']);
        }

        try {
            // Generate reset token
            $token = Str::random(64);
            
            // Store token in password_reset_tokens table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => hash('sha256', $token),
                    'created_at' => now()
                ]
            );

            // Create reset URL
            $resetUrl = URL::temporarySignedRoute(
                'password.reset',
                now()->addMinutes(60),
                ['token' => $token, 'email' => $request->email]
            );

            // Send WhatsApp notification
            $whatsappService = app(WhatsappNotificationService::class);
            $whatsappService->sendPasswordResetMessage($user, $resetUrl);

            return back()->with('status', 'Link reset password telah dikirim ke WhatsApp Anda.');
        } catch (\Exception $e) {
            Log::error('WhatsApp password reset failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Gagal mengirim pesan WhatsApp. Silakan coba lagi atau gunakan reset via email.']);
        }
    }
}
