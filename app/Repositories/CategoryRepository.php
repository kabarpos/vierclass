<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Query standar untuk tabel Category di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return Category::query()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}

