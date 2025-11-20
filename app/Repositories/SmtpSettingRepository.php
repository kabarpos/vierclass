<?php

namespace App\Repositories;

use App\Models\SmtpSetting;
use Illuminate\Database\Eloquent\Builder;

class SmtpSettingRepository implements SmtpSettingRepositoryInterface
{
    /**
     * Query standar untuk tabel SmtpSetting di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return SmtpSetting::query();
    }
}

