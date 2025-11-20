<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface SmtpSettingRepositoryInterface
{
    /**
     * Query standar untuk tabel SmtpSetting di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}

