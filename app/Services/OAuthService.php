<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\User;

class OAuthService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected SecurityService $securityService,
    ) {}

    /**
     * Menyelesaikan proses callback Google dan mengembalikan user.
     */
    public function resolveUserFromGoogle(Request $request): User
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $data = [
            'provider' => 'google',
            'oauth_id' => (string)($googleUser->getId() ?? ''),
            'email' => (string)($googleUser->getEmail() ?? ''),
            'name' => (string)($googleUser->getName() ?? ''),
        ];

        return $this->users->upsertFromOauth($data);
    }

    /**
     * Whitelist IP sementara pasca login, mengikuti kebijakan SecurityService.
     */
    public function postLoginWhitelist(Request $request, ?User $user = null): void
    {
        if (config('security.scanner.login_auto_whitelist_enabled', true)) {
            $ip = $this->securityService->resolveClientIp($request);
            if ($ip && $this->securityService->isValidIpAddress($ip)) {
                $this->securityService->addToWhitelist($ip);
                $this->securityService->logSecurityEvent('login_ip_whitelisted', $ip, $request->userAgent(), [
                    'ttl_minutes' => config('security.scanner.whitelist_ttl_minutes', 1440),
                    'user_id' => optional($user)->id,
                ]);
            }
        }
    }
}

