<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface SectionContentRepositoryInterface
{
    /**
     * Query standar untuk tabel SectionContent di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}

