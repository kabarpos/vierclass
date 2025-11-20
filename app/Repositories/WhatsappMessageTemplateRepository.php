<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Models\WhatsappMessageTemplate;

class WhatsappMessageTemplateRepository implements WhatsappMessageTemplateRepositoryInterface
{
    public function filamentTableQuery(): Builder
    {
        return WhatsappMessageTemplate::query();
    }
}

