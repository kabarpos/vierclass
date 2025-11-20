<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If not authenticated, let 'auth' middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Enforce active account with at least one verified channel
        if (!$user->isAccountActive()) {
            return redirect()->route('whatsapp.verification.status')
                ->with('warning', 'Akun Anda belum aktif. Silakan verifikasi melalui WhatsApp atau Email untuk melanjutkan.');
        }

        return $next($request);
    }
}