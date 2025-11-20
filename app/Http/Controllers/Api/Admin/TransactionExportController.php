<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Transaction;
use App\Repositories\RevenueRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportController
{
    public function export(Request $request, RevenueRepositoryInterface $revenues): StreamedResponse
    {
        // Otorisasi: hanya admin/super-admin
        $this->authorizeAccess();

        // Bangun query dasar + filter repository (mentor, course, tanggal by started_at)
        $query = $revenues->baseQueryForCurrentUser();

        $filters = array_filter($request->only(['mentor_id', 'course_id', 'from_date', 'to_date']), fn($v) => $v !== null && $v !== '');

        $query = $revenues->applyFilters($query, $filters);

        // Filter tambahan yang umum dipakai di admin API
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('search')) {
            $term = '%' . trim($request->string('search')) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn($s) => $s->where('name', 'like', $term)->orWhere('email', 'like', $term))
                  ->orWhereHas('course', fn($c) => $c->where('name', 'like', $term));
            });
        }

        // Sorting default berdasarkan started_at terbaru
        $query->latest('started_at');

        $response = new StreamedResponse(function () use ($query) {
            $output = fopen('php://output', 'w');
            // Tulis BOM agar kompatibel dengan Excel
            fprintf($output, "\xEF\xBB\xBF");

            // Header kolom CSV
            fputcsv($output, [
                'ID',
                'Student Name',
                'Student Email',
                'Course',
                'Started At',
                'Grand Total',
                'Admin Fee',
                'Discount',
                'Net Revenue',
                'Payment Type',
                'Is Paid',
            ]);

            // Stream data per baris menggunakan cursor untuk efisiensi
            foreach ($query->cursor() as $t) {
                /** @var Transaction $t */
                $net = (float) $t->grand_total_amount - (float) $t->admin_fee_amount - (float) $t->discount_amount;
                fputcsv($output, [
                    $t->id,
                    optional($t->student)->name,
                    optional($t->student)->email,
                    optional($t->course)->name,
                    optional($t->started_at)?->format('Y-m-d'),
                    $t->grand_total_amount,
                    $t->admin_fee_amount,
                    $t->discount_amount,
                    $net,
                    $t->payment_type,
                    $t->is_paid ? 'paid' : 'unpaid',
                ]);
            }

            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

        return $response;
    }

    protected function authorizeAccess(): void
    {
        // Gunakan policy yang sudah terdaftar
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()->hasAnyRole(['admin', 'super-admin']), 403);
    }
}
