<?php

namespace App\Repositories;

use App\Models\TripaySetting;
use Illuminate\Database\Eloquent\Builder;

interface TripaySettingRepositoryInterface
{
    /**
     * Mengambil konfigurasi Tripay aktif, jika ada.
     */
    public function getActive(): ?TripaySetting;

    /**
     * Mengambil konfigurasi Tripay sebagai array siap pakai.
     */
    public function getConfig(): array;

    /**
     * Query standar untuk tabel Filament TripaySettingResource
     */
    public function filamentTableQuery(): Builder;
}
