<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface WebsiteSettingRepositoryInterface
{
    /**
     * Query standar untuk tabel/record Filament.
     */
    public function filamentTableQuery(): Builder;

    /**
     * Ambil nilai default payment gateway dari WebsiteSetting.
     * Wajib mengembalikan salah satu dari: 'midtrans' atau 'tripay'.
     * Jika tidak tersedia atau terjadi kesalahan, fallback ke 'midtrans'.
     */
    public function getDefaultPaymentGateway(): string;
}
