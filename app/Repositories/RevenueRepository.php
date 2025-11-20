<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RevenueRepository implements RevenueRepositoryInterface
{
    public function baseQueryForCurrentUser(): Builder
    {
        $user = Auth::user();

        $query = Transaction::query()
            ->with(['student', 'course'])
            ->where('is_paid', true);

        // Jika mentor (bukan admin/super-admin), batasi hanya transaksi dari course yang di-mentori
        if ($user && $user->hasRole('mentor') && !$user->hasAnyRole(['admin', 'super-admin'])) {
            $query->whereHas('course.courseMentors', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query;
    }

    public function applyFilters(Builder $query, array $filters = []): Builder
    {
        // Logging ringan untuk validasi perilaku filter (hanya saat ada input)
        if (!empty($filters)) {
            \Log::info('Revenue filters applied', [
                'mentor_id' => $filters['mentor_id'] ?? null,
                'course_id' => $filters['course_id'] ?? null,
                'from' => $filters['from'] ?? ($filters['from_date'] ?? null),
                'to' => $filters['to'] ?? ($filters['to_date'] ?? null),
                'auth_user_id' => Auth::id(),
            ]);
        }
        // Filter mentor (untuk admin/super-admin)
        if (!empty($filters['mentor_id'])) {
            $mentorId = (int) $filters['mentor_id'];
            $query->whereHas('course.courseMentors', function (Builder $q) use ($mentorId) {
                $q->where('user_id', $mentorId);
            });
        }

        // Filter course
        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }

        // Filter rentang tanggal berdasarkan started_at (tanggal mulai akses/pembayaran)
        $from = $filters['from'] ?? ($filters['from_date'] ?? null);
        $to = $filters['to'] ?? ($filters['to_date'] ?? null);

        // Normalisasi ke awal/akhir hari agar inklusif terhadap seluruh hari yang dipilih
        $fromDateTime = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDateTime = $to ? Carbon::parse($to)->endOfDay() : null;

        if ($fromDateTime && $toDateTime) {
            $query->whereBetween('started_at', [$fromDateTime, $toDateTime]);
        } elseif ($fromDateTime) {
            $query->where('started_at', '>=', $fromDateTime);
        } elseif ($toDateTime) {
            $query->where('started_at', '<=', $toDateTime);
        }

        return $query;
    }

    public function summarize(array $filters = []): array
    {
        $query = $this->applyFilters($this->baseQueryForCurrentUser(), $filters);

        // Agregasi utama
        $gross = (float) $query->sum('grand_total_amount');
        $adminFee = (float) $query->sum('admin_fee_amount');
        $discount = (float) $query->sum('discount_amount');
        $count = (int) $query->count();

        // Net = gross - admin fee - discount
        $net = $gross - $adminFee - $discount;

        return [
            'gross_total' => $gross,
            'admin_fee_total' => $adminFee,
            'discount_total' => $discount,
            'net_total' => $net,
            'transactions_count' => $count,
        ];
    }
}
