<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface EmailMessageTemplateRepositoryInterface
{
    /**
     * Query standar untuk tabel Filament EmailMessageTemplateResource
     */
    public function filamentTableQuery(): Builder;
}

