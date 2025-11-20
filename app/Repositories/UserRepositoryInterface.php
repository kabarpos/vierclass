<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface UserRepositoryInterface
{
    /**
     * Query standar untuk tabel User di Filament Resources.
     */
    public function filamentTableQuery(): Builder;

    /**
     * Cari user berdasarkan provider OAuth dan ID.
     */
    public function findByOauth(string $provider, string $oauthId): ?\App\Models\User;

    /**
     * Cari user berdasarkan email.
     */
    public function findByEmail(string $email): ?\App\Models\User;

    /**
     * Upsert user dari data OAuth (Google). Mengembalikan user final.
     */
    public function upsertFromOauth(array $oauthData): \App\Models\User;
}
