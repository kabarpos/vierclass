<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Models\MidtransSetting;

class MidtransSettingRepository implements MidtransSettingRepositoryInterface
{
    public function filamentTableQuery(): Builder
    {
        return MidtransSetting::query();
    }
}

