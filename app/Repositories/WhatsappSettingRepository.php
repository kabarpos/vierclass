<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Models\WhatsappSetting;

class WhatsappSettingRepository implements WhatsappSettingRepositoryInterface
{
    public function filamentTableQuery(): Builder
    {
        return WhatsappSetting::query();
    }
}

