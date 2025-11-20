<?php

namespace App\Repositories;

use App\Models\PaymentReference;

interface PaymentReferenceRepositoryInterface
{
    public function createPending(array $data): PaymentReference;
    public function findByMerchantRef(string $merchantRef): ?PaymentReference;
    public function updateStatus(string $merchantRef, string $status): bool;
    public function attachGatewayReference(string $merchantRef, string $gatewayReference): bool;
    public function attachBookingId(string $merchantRef, string $bookingTrxId): bool;
    /**
     * Menautkan booking_trx_id ke PaymentReference berdasarkan kecocokan user, course, dan amount.
     * Prioritas: cari record status 'PAID' dengan paid_amount sama, booking_trx_id masih null.
     */
    public function attachBookingIdByMatch(int $userId, int $courseId, int $amount, string $bookingTrxId): bool;
    public function markPaidByMerchantRef(string $merchantRef, array $updates = []): bool;
    public function updateFromTripayCallback(string $merchantRef, array $payload): bool;
}
