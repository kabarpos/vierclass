<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface CategoryRepositoryInterface
{
    /**
     * Query standar untuk tabel Category di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}

