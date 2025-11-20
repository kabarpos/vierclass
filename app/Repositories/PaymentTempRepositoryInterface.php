<?php

namespace App\Repositories;

use App\Models\PaymentTemp;

interface PaymentTempRepositoryInterface
{
    /**
     * Membuat record PaymentTemp berdasarkan data yang diberikan.
     */
    public function createPaymentRecord(array $data): PaymentTemp;

    /**
     * Mencari PaymentTemp berdasarkan order_id.
     */
    public function findByOrderId(string $orderId): ?PaymentTemp;

    /**
     * Membersihkan record PaymentTemp yang sudah kedaluwarsa.
     * Mengembalikan jumlah record yang terhapus.
     */
    public function cleanupExpired(): int;

    /**
     * Menandai PaymentTemp sebagai completed (jika belum) berdasarkan order_id.
     * Mengembalikan true jika ada perubahan status.
     */
    public function markCompletedByOrderId(string $orderId): bool;

    /**
     * Menghapus PaymentTemp berdasarkan order_id.
     */
    public function deleteByOrderId(string $orderId): bool;

    /**
     * Update status PaymentTemp berdasarkan order_id secara atomik.
     * Mengembalikan true jika ada row yang terupdate.
     */
    public function updateStatusByOrderId(string $orderId, string $status): bool;
}
