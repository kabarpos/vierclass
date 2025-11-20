<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface WhatsappSettingRepositoryInterface
{
    /**
     * Query standar untuk tabel Filament WhatsappSettingResource
     */
    public function filamentTableQuery(): Builder;
}

