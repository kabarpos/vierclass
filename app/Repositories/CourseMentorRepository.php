<?php

namespace App\Repositories;

use App\Models\CourseMentor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseMentorRepository implements CourseMentorRepositoryInterface
{
    /**
     * Query standar untuk tabel CourseMentor di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return CourseMentor::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            // Eager load relasi yang dipakai di tabel untuk mencegah N+1
            ->with(['mentor', 'course']);
    }
}

