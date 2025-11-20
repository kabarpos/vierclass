<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface RoleRepositoryInterface
{
    /**
     * Query standar untuk tabel Filament RoleResource
     */
    public function filamentTableQuery(): Builder;
}

