<?php

namespace App\Repositories;

use App\Models\PaymentTemp;

class PaymentTempRepository implements PaymentTempRepositoryInterface
{
    public function createPaymentRecord(array $data): PaymentTemp
    {
        // Gunakan factory method pada model jika tersedia untuk menjaga konsistensi
        if (method_exists(PaymentTemp::class, 'createPaymentRecord')) {
            return PaymentTemp::createPaymentRecord($data);
        }

        return PaymentTemp::create($data);
    }

    public function findByOrderId(string $orderId): ?PaymentTemp
    {
        if (method_exists(PaymentTemp::class, 'findByOrderId')) {
            return PaymentTemp::findByOrderId($orderId);
        }

        return PaymentTemp::where('order_id', $orderId)->first();
    }

    public function cleanupExpired(): int
    {
        if (method_exists(PaymentTemp::class, 'cleanupExpired')) {
            return PaymentTemp::cleanupExpired();
        }

        // Fallback jika method tidak tersedia: hapus yang created_at lebih dari 24 jam
        return PaymentTemp::where('created_at', '<', now()->subDay())->delete();
    }

    public function markCompletedByOrderId(string $orderId): bool
    {
        // Atomic update untuk menghindari race condition
        $updated = PaymentTemp::where('order_id', $orderId)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'completed']);

        return $updated > 0;
    }

    public function deleteByOrderId(string $orderId): bool
    {
        $deleted = PaymentTemp::where('order_id', $orderId)->delete();
        return $deleted > 0;
    }

    public function updateStatusByOrderId(string $orderId, string $status): bool
    {
        // Hindari menimpa status 'completed' jika sudah selesai
        $updated = PaymentTemp::where('order_id', $orderId)
            ->where('status', '!=', 'completed')
            ->update(['status' => $status]);

        return $updated > 0;
    }
}
