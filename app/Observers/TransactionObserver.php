<?php

namespace App\Observers;

use App\Helpers\TransactionHelper;
use App\Models\Transaction;
use App\Services\WhatsappNotificationService;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    //

    public function creating($transaction)
    {
        // Only generate booking_trx_id if not already set (for manual creation)
        if (empty($transaction->booking_trx_id)) {
            $transaction->booking_trx_id = TransactionHelper::generateUniqueTrxId();
        }
    }


    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Send order completion notification via WhatsApp
        try {
            $whatsappService = app(WhatsappNotificationService::class);
            $result = $whatsappService->sendOrderCompletion($transaction);
            
            if ($result['success']) {
                Log::info('Order completion WhatsApp notification sent', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id
                ]);
            } else {
                Log::warning('Failed to send order completion WhatsApp notification', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Order completion WhatsApp notification exception', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Check if payment status changed to paid
        if ($transaction->isDirty('is_paid') && $transaction->is_paid) {
            try {
                $whatsappService = app(WhatsappNotificationService::class);
                $result = $whatsappService->sendPaymentReceivedNotification($transaction);
                
                if ($result['success']) {
                    Log::info('Payment received WhatsApp notification sent', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id
                    ]);
                } else {
                    Log::warning('Failed to send payment received WhatsApp notification', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'error' => $result['message'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Payment received WhatsApp notification exception', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Tautkan booking_trx_id ke PaymentReference agar korelasi lengkap
            try {
                /** @var \App\Repositories\PaymentReferenceRepositoryInterface $paymentRefRepo */
                $paymentRefRepo = app(\App\Repositories\PaymentReferenceRepositoryInterface::class);
                $linked = $paymentRefRepo->attachBookingIdByMatch(
                    (int) $transaction->user_id,
                    (int) $transaction->course_id,
                    (int) $transaction->grand_total_amount,
                    (string) $transaction->booking_trx_id
                );

                Log::info('[PaymentReference] Link booking_trx_id on transaction paid', [
                    'transaction_id' => $transaction->id,
                    'booking_trx_id' => $transaction->booking_trx_id,
                    'linked' => $linked,
                ]);
            } catch (\Throwable $t) {
                Log::warning('[PaymentReference] Gagal menautkan booking_trx_id pada update transaksi', [
                    'transaction_id' => $transaction->id,
                    'error' => $t->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
