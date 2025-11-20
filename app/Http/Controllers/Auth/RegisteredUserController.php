<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsappNotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\RegistrationEmailVerification;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],
        ]);

        // Create user with inactive status
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'whatsapp_number' => $request->whatsapp_number,
            'is_account_active' => false, // Account not active until verified
        ]);

        $user->assignRole('student');

        // Generate verification token
        $user->generateVerificationToken();

        // Send verification notifications
        try {
            $whatsappService = app(WhatsappNotificationService::class);
            $result = $whatsappService->sendRegistrationVerification($user);
            
            if ($result['success']) {
                // Send Email verification as well via templated service
                try {
                    $emailService = app(\App\Services\EmailNotificationService::class);
                    $emailResult = $emailService->sendRegistrationVerification($user);
                    if ($emailResult['success']) {
                        Log::info('Registration email verification sent successfully', [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    } else {
                        Log::warning('Failed to send registration email verification', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'error' => $emailResult['error'] ?? $emailResult['message'] ?? 'Unknown'
                        ]);
                    }
                } catch (\Exception $emailEx) {
                    Log::warning('Failed to send registration email verification', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $emailEx->getMessage()
                    ]);
                }
                Log::info('Registration verification WhatsApp sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                return redirect()->route('login')
                    ->with('success', 'Pendaftaran berhasil! Link verifikasi telah dikirim ke WhatsApp dan Email Anda. Verifikasi salah satu (WhatsApp atau Email) untuk mengaktifkan akun dan login. (Opsional: verifikasi keduanya untuk keamanan tambahan).');
            } else {
                Log::warning('Registration successful but WhatsApp notification failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                
                return redirect()->route('login')
                    ->with('warning', 'Pendaftaran berhasil, namun gagal mengirim notifikasi WhatsApp. Silakan hubungi administrator untuk mengaktifkan akun Anda.');
            }
        } catch (\Exception $e) {
            Log::error('Registration WhatsApp notification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('login')
                ->with('warning', 'Pendaftaran berhasil, namun terjadi kesalahan saat mengirim verifikasi. Silakan hubungi administrator.');
        }

        // Note: We don't fire the Registered event or auto-login the user
        // until they verify their account
    }
}
