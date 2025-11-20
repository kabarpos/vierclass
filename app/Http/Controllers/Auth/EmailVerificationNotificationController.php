<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(\resolve_post_login_redirect($request->user()));
        }
        // Gunakan service templated agar konsisten dengan token verifikasi dan konfigurasi SMTP
        try {
            $service = app(\App\Services\EmailNotificationService::class);
            $result = $service->sendRegistrationVerification($request->user());

            if ($result['success'] ?? false) {
                return back()->with('status', 'verification-link-sent');
            }

            return back()->with('warning', $result['message'] ?? 'Gagal mengirim verifikasi email.');
        } catch (\Throwable $e) {
            \Log::error('Failed to resend email verification via service', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mengirim verifikasi email.');
        }
    }
}
