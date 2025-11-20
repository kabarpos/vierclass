<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function findByBookingId(string $bookingId);
    public function create(array $data);
    public function firstOrCreateByBookingId(array $data);
    public function getUserTransactions(int $userId);
    public function userHasPurchasedCourse(int $userId, int $courseId): bool;

    /**
     * Query standar untuk tabel Transaction di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}
