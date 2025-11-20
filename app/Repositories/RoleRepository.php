<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function filamentTableQuery(): Builder
    {
        return Role::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

