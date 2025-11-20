<?php

namespace App\Repositories;

use App\Models\PaymentReference;
use Illuminate\Support\Facades\Log;

class PaymentReferenceRepository implements PaymentReferenceRepositoryInterface
{
    public function createPending(array $data): PaymentReference
    {
        $data['status'] = $data['status'] ?? 'UNPAID';
        return PaymentReference::create($data);
    }

    public function findByMerchantRef(string $merchantRef): ?PaymentReference
    {
        return PaymentReference::where('merchant_ref', $merchantRef)->first();
    }

    public function updateStatus(string $merchantRef, string $status): bool
    {
        return PaymentReference::where('merchant_ref', $merchantRef)
            ->update(['status' => $status]) > 0;
    }

    public function attachGatewayReference(string $merchantRef, string $gatewayReference): bool
    {
        return PaymentReference::where('merchant_ref', $merchantRef)
            ->update(['gateway_reference' => $gatewayReference]) > 0;
    }

    public function attachBookingId(string $merchantRef, string $bookingTrxId): bool
    {
        return PaymentReference::where('merchant_ref', $merchantRef)
            ->update(['booking_trx_id' => $bookingTrxId]) > 0;
    }

    /**
     * Menautkan booking_trx_id berdasarkan user/course/amount ke PaymentReference yang relevan.
     * Strategi:
     * - Pilih record status 'PAID' dengan paid_amount sama dan booking_trx_id masih null.
     * - Jika tidak ada, jangan melakukan update untuk menghindari salah taut.
     */
    public function attachBookingIdByMatch(int $userId, int $courseId, int $amount, string $bookingTrxId): bool
    {
        $query = PaymentReference::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereNull('booking_trx_id')
            ->where('status', 'PAID')
            ->where('paid_amount', $amount)
            ->orderByDesc('updated_at');

        $ref = $query->first();

        if (!$ref) {
            Log::info('[PaymentReference] Tidak ada kandidat untuk penautan booking_trx_id', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'amount' => $amount,
                'booking_trx_id' => $bookingTrxId,
            ]);
            return false;
        }

        $updated = PaymentReference::where('id', $ref->id)
            ->update(['booking_trx_id' => $bookingTrxId]);

        Log::info('[PaymentReference] Penautan booking_trx_id', [
            'payment_reference_id' => $ref->id,
            'merchant_ref' => $ref->merchant_ref,
            'booking_trx_id' => $bookingTrxId,
            'updated' => $updated > 0,
        ]);

        return $updated > 0;
    }

    public function markPaidByMerchantRef(string $merchantRef, array $updates = []): bool
    {
        $payload = array_merge([
            'status' => 'PAID',
        ], $updates);

        return PaymentReference::where('merchant_ref', $merchantRef)
            ->update($payload) > 0;
    }

    /**
     * Update kolom-kolom yang relevan dari payload callback Tripay.
     */
    public function updateFromTripayCallback(string $merchantRef, array $payload): bool
    {
        $data = [
            'status' => $payload['status'] ?? 'UNPAID',
            'gateway_reference' => $payload['reference'] ?? null,
            'paid_amount' => isset($payload['paid_amount']) ? (int) $payload['paid_amount'] : null,
            'payment_method' => $payload['payment_method'] ?? null,
            'payment_channel' => $payload['payment_channel'] ?? null,
            'callback_received_at' => now(),
        ];

        Log::info('[PaymentReference] Update from Tripay callback', [
            'merchant_ref' => $merchantRef,
            'payload' => $payload,
        ]);

        return PaymentReference::where('merchant_ref', $merchantRef)
            ->update($data) > 0;
    }
}
