<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface CourseMentorRepositoryInterface
{
    /**
     * Query standar untuk tabel CourseMentor di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}

