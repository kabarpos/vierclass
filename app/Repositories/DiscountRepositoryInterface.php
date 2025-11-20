<?php

namespace App\Repositories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface DiscountRepositoryInterface
{
    /**
     * Buat diskon baru.
     */
    public function create(array $data): Discount;

    /**
     * Update data diskon.
     */
    public function update(Discount $discount, array $data): Discount;

    /**
     * Hapus diskon.
     */
    public function delete(Discount $discount): bool;

    /**
     * Ambil semua diskon aktif dan tersedia.
     */
    public function getActive(): Collection;

    /**
     * Cari diskon berdasarkan kode (aktif dan tersedia).
     */
    public function findByCode(string $code): ?Discount;

    /**
     * Cari diskon berdasarkan ID.
     */
    public function findById(int $id): ?Discount;

    /**
     * Increment penggunaan diskon secara aman.
     */
    public function incrementUsage(Discount $discount): bool;

    /**
     * Cek apakah kode diskon sudah ada (unik secara global).
     */
    public function existsByCode(string $code): bool;

    /**
     * Query standar untuk tabel Discount di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}
