<?php

namespace App\Repositories;

use App\Models\SectionContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionContentRepository implements SectionContentRepositoryInterface
{
    /**
     * Query standar untuk tabel SectionContent di Filament Resources.
     * Mengikutkan pembatasan untuk role mentor agar hanya melihat konten dari course yang di-mentori.
     */
    public function filamentTableQuery(): Builder
    {
        $query = SectionContent::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();
        if ($user && $user->hasRole('mentor') && !$user->hasAnyRole(['admin', 'super-admin'])) {
            $query->whereHas('courseSection.course.courseMentors', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->with(['courseSection.course']);
    }
}

