<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * Verify WhatsApp via token (publik)
     */
    public function verifyWhatsapp(Request $request, $id, $token)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->verification_token !== $token) {
                return redirect()->route('login')
                    ->with('error', 'Token verifikasi tidak valid atau sudah kadaluarsa.');
            }

            if ($user->isFullyVerified()) {
                return redirect()->route('login')
                    ->with('info', 'Akun Anda sudah terverifikasi sebelumnya.');
            }

            // Verifikasi WhatsApp saja
            $user->verifyWhatsapp();

            // Hapus token hanya jika sudah fully verified
            if ($user->isFullyVerified()) {
                $user->update(['verification_token' => null]);
            }

            Log::info('User WhatsApp verified successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return redirect()->route('login')
                ->with('success', 'Nomor WhatsApp berhasil diverifikasi. Akun Anda telah diaktifkan dan Anda dapat login sekarang. (Opsional: verifikasi email untuk keamanan tambahan).');

        } catch (\Exception $e) {
            Log::error('WhatsApp verification failed', [
                'user_id' => $id,
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan saat verifikasi WhatsApp. Silakan hubungi administrator.');
        }
    }

    /**
     * Verify Email via token (publik)
     */
    public function verifyEmail(Request $request, $id, $token)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->verification_token !== $token) {
                return redirect()->route('login')
                    ->with('error', 'Token verifikasi tidak valid atau sudah kadaluarsa.');
            }

            if ($user->isFullyVerified()) {
                return redirect()->route('login')
                    ->with('info', 'Akun Anda sudah terverifikasi sebelumnya.');
            }

            // Verifikasi Email saja
            $user->verifyEmail();

            // Hapus token hanya jika sudah fully verified
            if ($user->isFullyVerified()) {
                $user->update(['verification_token' => null]);
            }

            Log::info('User email verified successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return redirect()->route('login')
                ->with('success', 'Email berhasil diverifikasi. Akun Anda telah diaktifkan dan Anda dapat login sekarang. (Opsional: verifikasi WhatsApp untuk keamanan tambahan).');

        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'user_id' => $id,
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan saat verifikasi email. Silakan hubungi administrator.');
        }
    }
    
    /**
     * Resend verification link
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        
        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return back()->with('error', 'Email tidak ditemukan.');
            }
            
            if ($user->isFullyVerified()) {
                return back()->with('info', 'Akun Anda sudah terverifikasi.');
            }
            
            // Generate new verification token if not exists
            if (!$user->verification_token) {
                $user->generateVerificationToken();
            }
            
            // Send verification notification
            $whatsappService = app(\App\Services\WhatsappNotificationService::class);
            $result = $whatsappService->sendRegistrationVerification($user);
            
            if ($result['success']) {
                return back()->with('success', 'Link verifikasi telah dikirim ulang ke WhatsApp Anda.');
            } else {
                return back()->with('warning', 'Link verifikasi dibuat, namun gagal dikirim ke WhatsApp. Silakan hubungi administrator.');
            }
            
        } catch (\Exception $e) {
            Log::error('Resend verification failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
    
    /**
     * Show verification status page
     */
    public function status()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        return view('auth.verification-status', compact('user'));
    }
}
