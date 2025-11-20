<?php

namespace App\Repositories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiscountRepository implements DiscountRepositoryInterface
{
    public function create(array $data): Discount
    {
        return Discount::create($data);
    }

    public function update(Discount $discount, array $data): Discount
    {
        $discount->update($data);
        return $discount->fresh();
    }

    public function delete(Discount $discount): bool
    {
        return (bool) $discount->delete();
    }

    public function getActive(): Collection
    {
        return Discount::active()->available()->get();
    }

    public function findByCode(string $code): ?Discount
    {
        return Discount::where('code', $code)
            ->active()
            ->available()
            ->first();
    }

    public function findById(int $id): ?Discount
    {
        return Discount::find($id);
    }

    public function incrementUsage(Discount $discount): bool
    {
        // Hindari race condition sederhana dengan update atomik berbasis ID
        $affected = DB::table($discount->getTable())
            ->where('id', $discount->id)
            ->update(['used_count' => DB::raw('used_count + 1')]);
        return $affected > 0;
    }

    public function existsByCode(string $code): bool
    {
        return Discount::where('code', $code)->exists();
    }

    /**
     * Query standar untuk tabel Discount di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return Discount::query();
    }
}
