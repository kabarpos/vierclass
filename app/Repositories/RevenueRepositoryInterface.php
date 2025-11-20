<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface RevenueRepositoryInterface
{
    /**
     * Query dasar transaksi yang sudah dibayar, dibatasi sesuai peran pengguna saat ini.
     */
    public function baseQueryForCurrentUser(): Builder;

    /**
     * Terapkan filter (mentor, course, rentang tanggal) ke query.
     * Mendukung parameter tanggal 'from'/'to' maupun 'from_date'/'to_date', semuanya dipetakan ke kolom started_at.
     *
     * @param Builder $query
     * @param array{mentor_id?: int, course_id?: int, from?: string, to?: string, from_date?: string, to_date?: string} $filters
     */
    public function applyFilters(Builder $query, array $filters = []): Builder;

    /**
     * Ringkasan agregasi pendapatan berdasarkan filter.
     *
     * @param array{mentor_id?: int, course_id?: int, from?: string, to?: string} $filters
     * @return array{gross_total: float, admin_fee_total: float, discount_total: float, net_total: float, transactions_count: int}
     */
    public function summarize(array $filters = []): array;
}
