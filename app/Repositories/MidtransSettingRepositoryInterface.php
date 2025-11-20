<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface MidtransSettingRepositoryInterface
{
    /**
     * Query standar untuk tabel Filament MidtransSettingResource
     */
    public function filamentTableQuery(): Builder;
}

