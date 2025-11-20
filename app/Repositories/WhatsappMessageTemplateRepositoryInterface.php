<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface WhatsappMessageTemplateRepositoryInterface
{
    /**
     * Query standar untuk tabel Filament WhatsappMessageTemplateResource
     */
    public function filamentTableQuery(): Builder;
}

