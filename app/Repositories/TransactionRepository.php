<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function findByBookingId(string $bookingId)
    {
        return Transaction::where('booking_trx_id', $bookingId)->first();
    }

    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function firstOrCreateByBookingId(array $data)
    {
        return Transaction::firstOrCreate([
            'booking_trx_id' => $data['booking_trx_id']
        ], $data);
    }

    public function getUserTransactions(int $userId)
    {
        return Cache::remember('transactions:user:' . $userId, now()->addMinutes(2), function () use ($userId) {
            return Transaction::with(['course', 'discount'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function userHasPurchasedCourse(int $userId, int $courseId): bool
    {
        return Transaction::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('is_paid', true)
            ->exists();
    }

    /**
     * Query standar untuk tabel Transaction di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return Transaction::query()
            ->with(['student', 'course', 'discount'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
