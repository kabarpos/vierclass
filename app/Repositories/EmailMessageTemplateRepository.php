<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Models\EmailMessageTemplate;

class EmailMessageTemplateRepository implements EmailMessageTemplateRepositoryInterface
{
    public function filamentTableQuery(): Builder
    {
        return EmailMessageTemplate::query();
    }
}

