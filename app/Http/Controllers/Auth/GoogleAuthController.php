<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(protected OAuthService $oauth)
    {
    }

    /**
     * Redirect ke Google untuk proses OAuth.
     */
    public function redirect(): RedirectResponse
    {
        Log::info('OAuth Google redirect initiated');
        // Tambahkan scopes eksplisit untuk memastikan email selalu tersedia
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Callback dari Google, lakukan upsert user dan login.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $user = $this->oauth->resolveUserFromGoogle($request);

            Auth::login($user, true);
            $request->session()->regenerate();

            $this->oauth->postLoginWhitelist($request, $user);

            Log::info('OAuth Google login success', ['user_id' => $user->id]);
            return redirect()->intended(\resolve_post_login_redirect(auth()->user()))
                ->with('success', 'Berhasil login dengan Google.');
        } catch (\Throwable $e) {
            Log::error('OAuth Google login failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'code' => method_exists($e, 'getCode') ? $e->getCode() : null,
                'file' => method_exists($e, 'getFile') ? $e->getFile() : null,
                'line' => method_exists($e, 'getLine') ? $e->getLine() : null,
            ]);
            return redirect()->route('login')
                ->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }
    }
}
