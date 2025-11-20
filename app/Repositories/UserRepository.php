<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Query standar untuk tabel User di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return User::query()
            // Eager load roles untuk menghindari N+1 di kolom roles.name
            ->with(['roles'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public function findByOauth(string $provider, string $oauthId): ?User
    {
        return User::query()
            ->where('oauth_provider', $provider)
            ->where('oauth_id', $oauthId)
            ->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function upsertFromOauth(array $oauthData): User
    {
        $provider = $oauthData['provider'] ?? null;
        $oauthId = $oauthData['oauth_id'] ?? null;
        $email = $oauthData['email'] ?? null;
        $name = $oauthData['name'] ?? null;

        if (!$provider || !$oauthId || !$email) {
            throw new \InvalidArgumentException('Data OAuth tidak lengkap.');
        }

        $user = $this->findByOauth($provider, $oauthId);
        if ($user) {
            // Perbarui data minimal yang relevan
            $user->update([
                'name' => $name ?: $user->name,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'is_account_active' => true,
            ]);
            return $user;
        }

        // Jika belum ada by oauth, coba cocokkan via email
        $user = $this->findByEmail($email);
        if ($user) {
            $user->update([
                'oauth_provider' => $provider,
                'oauth_id' => $oauthId,
                'name' => $name ?: $user->name,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'is_account_active' => true,
            ]);
            return $user;
        }

        // Buat user baru sebagai student (password acak)
        $user = User::create([
            'name' => $name ?: explode('@', $email)[0],
            'email' => $email,
            // Simpan password acak dalam bentuk hash agar aman
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
            'oauth_provider' => $provider,
            'oauth_id' => $oauthId,
            'email_verified_at' => now(),
            'is_account_active' => true,
        ]);

        // Assign default role student jika tersedia
        try {
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('student');
            }
        } catch (\Throwable $e) {
            // Abaikan jika role belum tersedia; tidak mengganggu login
        }

        return $user;
    }
}
