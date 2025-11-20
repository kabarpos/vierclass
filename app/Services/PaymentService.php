<?php

namespace App\Services;

use Exception;
use App\Helpers\TransactionHelper;
use App\Models\Course;
use App\Models\Discount;
use App\Models\PaymentReference;
use App\Models\User;
use App\Mail\CoursePurchaseConfirmation;
use App\Notifications\CoursePurchasedNotification;
use App\Services\WhatsappNotificationService;
use App\Services\DiscountService;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\PaymentTempRepositoryInterface;
use App\Repositories\DiscountRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    protected $midtransService;
    protected $pricingRepository;
    protected $transactionRepository;
    protected $whatsappService;
    protected $discountService;
    protected $courseRepository;
    protected $paymentTempRepository;
    protected $discountRepository;

    public function __construct(
        MidtransService $midtransService,
        TransactionRepositoryInterface $transactionRepository,
        WhatsappNotificationService $whatsappService,
        DiscountService $discountService,
        CourseRepositoryInterface $courseRepository,
        PaymentTempRepositoryInterface $paymentTempRepository,
        DiscountRepositoryInterface $discountRepository
    )
    {
        $this->midtransService = $midtransService;
        $this->transactionRepository = $transactionRepository;
        $this->whatsappService = $whatsappService;
        $this->discountService = $discountService;
        $this->courseRepository = $courseRepository;
        $this->paymentTempRepository = $paymentTempRepository;
        $this->discountRepository = $discountRepository;
    }

    /**
     * Create payment for course purchase
     */
    public function createCoursePayment(int $courseId)
    {
        // ENHANCED LOGGING: Log awal proses payment
        Log::info('=== PAYMENT SERVICE START ===', [
            'course_id' => $courseId,
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString()
        ]);
        
        $user = Auth::user();
        $course = $this->courseRepository->findById((int) $courseId);
        if (!$course) {
            Log::error('Course not found via repository', [
                'course_id' => $courseId,
                'user_id' => $user?->id,
            ]);
            throw new Exception('Course not found');
        }
        
        Log::info('Course and user loaded', [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_price' => $course->price,
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
        
        // Get discount from session if available
        $appliedDiscount = session()->get('applied_discount');
        $discountAmount = 0;
        $discountId = null;
        
        Log::info('Checking session for discount', [
            'session_applied_discount' => $appliedDiscount,
            'session_all_data' => session()->all()
        ]);
        
        if ($appliedDiscount) {
            Log::info('Discount found in session, calculating amount', [
                'discount_data' => $appliedDiscount,
                'course_price' => $course->price
            ]);
            
            $discountId = $appliedDiscount['id'] ?? null;
            
            // SELALU hitung ulang discount_amount dari applied_discount
            // JANGAN bergantung pada session discount_amount yang bisa tidak sinkron
            if (isset($appliedDiscount['type']) && isset($appliedDiscount['value'])) {
                if ($appliedDiscount['type'] === 'percentage') {
                    $discountAmount = ($course->price * $appliedDiscount['value']) / 100;
                    Log::info('Percentage discount calculated', [
                        'percentage' => $appliedDiscount['value'],
                        'calculated_amount' => $discountAmount
                    ]);
                    // Apply maximum discount limit if exists
                    if (isset($appliedDiscount['maximum_discount']) && $appliedDiscount['maximum_discount'] > 0) {
                        $originalAmount = $discountAmount;
                        $discountAmount = min($discountAmount, $appliedDiscount['maximum_discount']);
                        Log::info('Maximum discount limit applied', [
                            'original_amount' => $originalAmount,
                            'max_limit' => $appliedDiscount['maximum_discount'],
                            'final_amount' => $discountAmount
                        ]);
                    }
                } else {
                    $discountAmount = min($appliedDiscount['value'], $course->price);
                    Log::info('Fixed discount applied', [
                        'fixed_amount' => $discountAmount
                    ]);
                }
            }
            
            // Log untuk debugging
            Log::info('Final discount calculation', [
                'course_id' => $course->id,
                'course_price' => $course->price,
                'applied_discount' => $appliedDiscount,
                'calculated_discount_amount' => $discountAmount,
                'session_discount_amount' => session()->get('discount_amount', 'not_set'),
                'discount_id' => $discountId,
                'discount_type' => $appliedDiscount['type'],
                'discount_value' => $appliedDiscount['value'],
                'final_price' => $course->price - $discountAmount
            ]);
        } else {
            Log::warning('No discount found in session', [
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'course_id' => $courseId
            ]);
        }
        
        $adminFeeAmount = $course->admin_fee_amount ?? 0;
        $subTotal = $course->price;
        $grandTotal = $subTotal + $adminFeeAmount - $discountAmount;

        // Prepare item details with discount consideration
        $itemDetails = [
            [
                'id' => $course->id,
                'price' => (int) $course->price,
                'quantity' => 1,
                'name' => $course->name,
            ]
        ];
        
        // Add admin fee if exists
        if ($adminFeeAmount > 0) {
            $itemDetails[] = [
                'id' => 'admin_fee',
                'price' => (int) $adminFeeAmount,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            ];
        }
        
        // Add discount as negative item if exists
        if ($discountAmount > 0) {
            $itemDetails[] = [
                'id' => 'discount',
                'price' => -(int) $discountAmount,
                'quantity' => 1,
                'name' => 'Diskon: ' . ($appliedDiscount['name'] ?? 'Diskon'),
            ];
        }

        // Prepare custom_expiry data
        $customExpiryData = [
            'admin_fee_amount' => $adminFeeAmount,
            'discount_amount' => $discountAmount,
            'discount_id' => $discountId
        ];
        
        Log::info('Preparing Midtrans parameters', [
            'order_id' => TransactionHelper::generateUniqueTrxId(),
            'gross_amount' => $grandTotal,
            'item_details' => $itemDetails,
            'custom_expiry_data' => $customExpiryData,
            'user_id' => $user->id,
            'course_id' => $courseId
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => TransactionHelper::generateUniqueTrxId(),
                'gross_amount' => (int) $grandTotal,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->whatsapp_number ?? '089998501293218'
            ],
            'item_details' => $itemDetails,
            'custom_field1' => $user->id,
            'custom_field2' => $courseId,
            'custom_field3' => 'course', // Mark as course purchase
            'custom_expiry' => json_encode($customExpiryData)
        ];
        
        Log::info('Final Midtrans parameters', [
            'params' => $params,
            'custom_expiry_json' => json_encode($customExpiryData)
        ]);

        $orderId = $params['transaction_details']['order_id'];
        $snapToken = $this->midtransService->createSnapToken($params);
        
        Log::info('Snap token created', [
            'order_id' => $orderId,
            'snap_token_length' => strlen($snapToken ?? ''),
            'success' => !empty($snapToken)
        ]);
        
        // Save payment data to temporary table for reliable access during webhook
        if ($snapToken) {
            try {
                $this->paymentTempRepository->createPaymentRecord([
                    'order_id' => $orderId,
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                    'sub_total_amount' => $subTotal,
                    'admin_fee_amount' => $adminFeeAmount,
                    'discount_amount' => $discountAmount,
                    'discount_id' => $discountId,
                    'grand_total_amount' => $grandTotal,
                    'snap_token' => $snapToken,
                    'discount_data' => $appliedDiscount
                ]);
                
                Log::info('Payment temp record created successfully', [
                    'order_id' => $orderId,
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                    'discount_amount' => $discountAmount,
                    'discount_id' => $discountId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create payment temp record', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        return $snapToken;
    }

    public function handlePaymentNotification()
    {
        Log::info('Processing Midtrans notification...');
        
        $notification = $this->midtransService->handleNotification();
        
        Log::info('Received Midtrans notification:', $notification);

        // Verify signature terlebih dahulu
        if (!$this->midtransService->verifySignature($notification)) {
            Log::warning('Midtrans notification signature invalid', [
                'order_id' => $notification['order_id'] ?? null,
                'status_code' => $notification['status_code'] ?? null,
            ]);
            // Jangan proses lebih lanjut jika signature tidak valid
            return 'invalid_signature';
        }

        // Pastikan tipe pembelian sesuai (opsional pengaman tambahan)
        if (($notification['custom_field3'] ?? 'course') !== 'course') {
            Log::warning('Notification ignored due to unsupported purchase type', [
                'custom_field3' => $notification['custom_field3'] ?? null,
                'order_id' => $notification['order_id'] ?? null,
            ]);
            return 'ignored';
        }

        if (in_array($notification['transaction_status'], ['capture', 'settlement'])) {
            Log::info('Transaction status is valid for processing: ' . $notification['transaction_status']);
            
            // Only handle course purchases now
            $courseId = (int) ($notification['custom_field2'] ?? 0);
            $course = $this->courseRepository->findById($courseId);
            if (!$course) {
                Log::error('Course not found in notification processing', [
                    'course_id' => $courseId,
                    'order_id' => $notification['order_id'] ?? null,
                ]);
                return null;
            }
            Log::info('Found course:', ['id' => $course->id, 'name' => $course->name]);
            $result = $this->createCourseTransaction($notification, $course);
            // Jika null, berarti gagal validasi amount/payload, jangan lanjut
            if ($result === null) {
                Log::warning('Transaction rejected due to validation failure (amount/payload mismatch)', [
                    'order_id' => $notification['order_id'] ?? null,
                ]);
                return 'invalid_amount';
            }
            
            // Kirim notifikasi hanya jika transaksi baru dibuat (hindari duplikasi)
            if ($result && (property_exists($result, 'wasRecentlyCreated') ? (bool) $result->wasRecentlyCreated : true)) {
                $this->sendCoursePurchaseConfirmationEmail($result, $course);
            }
            
            Log::info('Transaction creation result:', ['success' => $result !== null]);
        } else {
            Log::warning('Transaction status not processed: ' . $notification['transaction_status']);
        }

        return $notification['transaction_status'];
    }

    /**
     * Create transaction for course purchase
     */
    protected function createCourseTransaction(array $notification, Course $course)
    {
        Log::info('Creating course transaction with data:', $notification);
        
        // Idempotensi: Jika transaksi dengan booking_trx_id sudah ada, jangan buat ulang
        try {
            $existing = $this->transactionRepository->findByBookingId($notification['order_id']);
        } catch (\Throwable $e) {
            $existing = null;
            Log::warning('Failed to check existing transaction by booking_trx_id', [
                'order_id' => $notification['order_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        if ($existing) {
            Log::info('Duplicate webhook detected: transaction already exists', [
                'order_id' => $notification['order_id'],
                'transaction_id' => $existing->id,
                'user_id' => $existing->user_id,
                'course_id' => $existing->course_id,
            ]);
            // Opsional: update status payment_temp agar tidak diproses lagi
            try {
                $this->paymentTempRepository->markCompletedByOrderId($notification['order_id']);
            } catch (\Throwable $e) {
                Log::warning('Failed to mark payment_temp as completed for existing transaction', [
                    'order_id' => $notification['order_id'],
                    'error' => $e->getMessage(),
                ]);
            }
            return $existing;
        }

        // Get admin fee amount from course model
        $adminFeeAmount = $course->admin_fee_amount ?? 0;
        
        // Try to get discount information from custom_expiry first
        $customExpiry = json_decode($notification['custom_expiry'] ?? '{}', true);
        $discountAmount = $customExpiry['discount_amount'] ?? 0;
        $discountId = $customExpiry['discount_id'] ?? null;
        
        // If custom_expiry is null or empty, fallback to payment_temp table
        if (empty($notification['custom_expiry']) || ($discountAmount == 0 && $discountId === null)) {
            $paymentTemp = $this->paymentTempRepository->findByOrderId($notification['order_id']);
            if ($paymentTemp) {
                $discountAmount = $paymentTemp->discount_amount ?? 0;
                $discountId = $paymentTemp->discount_id;
                $adminFeeAmount = $paymentTemp->admin_fee_amount ?? $adminFeeAmount;
                
                Log::info('=== USING PAYMENT_TEMP DATA (CUSTOM_EXPIRY FALLBACK) ===', [
                    'order_id' => $notification['order_id'],
                    'payment_temp_found' => true,
                    'discount_amount_from_temp' => $discountAmount,
                    'discount_id_from_temp' => $discountId,
                    'admin_fee_from_temp' => $adminFeeAmount,
                    'discount_data' => $paymentTemp->getDiscountInfo()
                ]);
            } else {
                Log::warning('=== NO PAYMENT_TEMP DATA FOUND ===', [
                    'order_id' => $notification['order_id'],
                    'custom_expiry_empty' => empty($notification['custom_expiry']),
                    'fallback_failed' => true
                ]);
            }
        }
        
        Log::info('=== FINAL DISCOUNT DATA FOR TRANSACTION ===', [
            'notification_order_id' => $notification['order_id'] ?? 'unknown',
            'raw_custom_expiry' => $notification['custom_expiry'] ?? 'null',
            'custom_expiry_parsed' => $customExpiry,
            'final_discount_amount' => $discountAmount,
            'final_discount_id' => $discountId,
            'data_source' => empty($notification['custom_expiry']) ? 'payment_temp' : 'custom_expiry'
        ]);

        // == SECURITY CHECKS ==
        // Ambil payment_temp selalu untuk verifikasi payload dan amount
        $paymentTempRecord = null;
        try {
            $paymentTempRecord = $this->paymentTempRepository->findByOrderId($notification['order_id']);
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch payment_temp for validation', [
                'order_id' => $notification['order_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        // Hitung total yang diharapkan berdasarkan harga course, admin fee, dan diskon
        $expectedGrandTotal = (int) $course->price + (int) $adminFeeAmount - (int) $discountAmount;
        $paidAmount = (int) round((float) ($notification['gross_amount'] ?? 0));

        Log::info('=== VALIDATING PAYMENT AMOUNT AND PAYLOAD ===', [
            'order_id' => $notification['order_id'] ?? null,
            'computed_expected_grand_total' => $expectedGrandTotal,
            'midtrans_gross_amount' => $paidAmount,
            'has_payment_temp' => (bool) $paymentTempRecord,
            'payment_temp_user_id' => $paymentTempRecord?->user_id,
            'payment_temp_course_id' => $paymentTempRecord?->course_id,
            'notification_user_id' => (int) ($notification['custom_field1'] ?? 0),
            'notification_course_id' => (int) ($notification['custom_field2'] ?? 0),
        ]);

        // Validasi konsistensi user dan course dengan payment_temp jika ada
        if ($paymentTempRecord) {
            $notifUserId = (int) ($notification['custom_field1'] ?? 0);
            $notifCourseId = (int) ($notification['custom_field2'] ?? 0);
            if ((int) $paymentTempRecord->user_id !== $notifUserId || (int) $paymentTempRecord->course_id !== $notifCourseId) {
                Log::error('PAYMENT PAYLOAD MISMATCH: user/course tidak sesuai dengan payment_temp', [
                    'order_id' => $notification['order_id'] ?? null,
                    'payment_temp_user_id' => (int) $paymentTempRecord->user_id,
                    'payment_temp_course_id' => (int) $paymentTempRecord->course_id,
                    'notification_user_id' => $notifUserId,
                    'notification_course_id' => $notifCourseId,
                ]);
                try {
                    $this->paymentTempRepository->updateStatusByOrderId(
                        $notification['order_id'],
                        'flagged_payload_mismatch'
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to mark payment_temp as flagged_payload_mismatch', [
                        'order_id' => $notification['order_id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
                // Tolak pembuatan transaksi karena payload tidak valid
                return null;
            }
        }

        // Validasi jumlah dibayar harus sama dengan perhitungan server
        if ($paidAmount !== $expectedGrandTotal) {
            Log::error('PAID AMOUNT MISMATCH: gross_amount Midtrans tidak sama dengan perhitungan server', [
                'order_id' => $notification['order_id'] ?? null,
                'expected' => $expectedGrandTotal,
                'actual' => $paidAmount,
            ]);
            if ($paymentTempRecord) {
                try {
                    $this->paymentTempRepository->updateStatusByOrderId(
                        $notification['order_id'],
                        'flagged_amount_mismatch'
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to mark payment_temp as flagged_amount_mismatch', [
                        'order_id' => $notification['order_id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            // Tolak pembuatan transaksi karena jumlah tidak valid
            return null;
        }
        
        $transactionData = [
            'user_id' => $notification['custom_field1'],
            'pricing_id' => null, // No pricing for course purchase
            'course_id' => $notification['custom_field2'],
            'sub_total_amount' => $course->price,
            'admin_fee_amount' => $adminFeeAmount,
            'discount_amount' => $discountAmount,
            'discount_id' => $discountId,
            // Simpan nilai yang telah tervalidasi
            'grand_total_amount' => $paidAmount,
            'payment_type' => 'Midtrans',
            'is_paid' => true,
            'booking_trx_id' => $notification['order_id'],
            'started_at' => now(),
            'ended_at' => null, // Course purchases have lifetime access
        ];
        
        Log::info('Course transaction data to be created:', $transactionData);

        try {
            // Gunakan firstOrCreate berbasis booking_trx_id untuk mengurangi race condition
            $transaction = $this->transactionRepository->firstOrCreateByBookingId($transactionData);
            
            $recentlyCreated = property_exists($transaction, 'wasRecentlyCreated') ? (bool) $transaction->wasRecentlyCreated : true;
            Log::info('Course transaction created or found:', [
                'id' => $transaction->id,
                'booking_trx_id' => $transaction->booking_trx_id,
                'user_id' => $transaction->user_id,
                'course_id' => $transaction->course_id,
                'was_recently_created' => $recentlyCreated,
            ]);
            
            // Increment discount usage counter if discount was used
            if ($discountId && $recentlyCreated) {
                try {
                    $discount = $this->discountRepository->findById((int) $discountId);
                    if ($discount) {
                        $this->discountService->useDiscount($discount);
                        Log::info('Discount usage incremented successfully:', [
                            'discount_id' => $discountId,
                            'discount_code' => $discount->code,
                            'new_used_count' => $discount->fresh()->used_count,
                            'transaction_id' => $transaction->id
                        ]);
                    }
                } catch (\Exception $discountError) {
                    Log::warning('Failed to increment discount usage:', [
                        'discount_id' => $discountId,
                        'transaction_id' => $transaction->id,
                        'error' => $discountError->getMessage()
                    ]);
                }
            }
            
            // Clean up payment_temp record after successful transaction creation
            if ($recentlyCreated) {
                try {
                    $deleted = $this->paymentTempRepository->deleteByOrderId($notification['order_id']);
                    Log::info('Payment temp record cleaned up', [
                        'order_id' => $notification['order_id'],
                        'deleted' => $deleted
                    ]);
                } catch (\Exception $cleanupError) {
                    Log::warning('Failed to cleanup payment temp record', [
                        'order_id' => $notification['order_id'],
                        'error' => $cleanupError->getMessage()
                    ]);
                }
            }
            
            return $transaction;
        } catch (Exception $e) {
            Log::error('Failed to create course transaction:', [
                'error' => $e->getMessage(),
                'data' => $transactionData
            ]);
            throw $e;
        }
    }
    
    /**
     * Send course purchase confirmation email
     */
    protected function sendCoursePurchaseConfirmationEmail($transaction, Course $course)
    {
        try {
            $user = User::findOrFail($transaction->user_id);
            
            Log::info('Sending course purchase notifications', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'transaction_id' => $transaction->id
            ]);
            
            // Pastikan konfigurasi SMTP aktif diterapkan sebelum pengiriman email
            try {
                app(\App\Services\MailketingService::class)->applyMailConfig();
            } catch (\Throwable $cfgEx) {
                Log::warning('Failed to apply SMTP config before sending purchase email', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'error' => $cfgEx->getMessage(),
                ]);
            }
            
            // Send custom email template, pastikan pakai mailer SMTP
            Mail::mailer('smtp')->to($user->email)->send(new CoursePurchaseConfirmation($user, $course, $transaction));
            
            // Also send notification (for database logging and potential future channels)
            $user->notify(new CoursePurchasedNotification($course, $transaction));
            
            // Send WhatsApp notification
            $this->whatsappService->sendCoursePurchaseNotification($transaction, $course);
            
            Log::info('Course purchase notifications sent successfully', [
                'email' => $user->email,
                'course' => $course->name
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to send course purchase notifications:', [
                'error' => $e->getMessage(),
                'user_id' => $transaction->user_id ?? null,
                'course_id' => $course->id
            ]);
            // Don't throw the exception as notification failure shouldn't break the payment flow
        }
    }

    /**
     * Membuat transaksi Tripay close payment untuk pembelian course.
     * Tidak mengganggu flow Midtrans; metode baru berdiri sendiri.
     */
    public function createTripayTransaction(array $params): array
    {
        $tripay = app(\App\Services\TripayService::class);

        // Normalisasi parameter
        $merchantRef = $params['merchant_ref'] ?? ('INV-' . now()->format('YmdHis') . '-' . ($params['user_id'] ?? 'guest'));
        $amount = (int) ($params['amount'] ?? 0);

        $payload = [
            'method' => $params['method'],
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $params['customer_name'],
            'customer_email' => $params['customer_email'],
            'customer_phone' => $params['customer_phone'] ?? '',
            'order_items' => $params['order_items'],
            'return_url' => $params['return_url'] ?? url('/front/checkout-success'),
            'expired_time' => $params['expired_time'] ?? (time() + 24 * 60 * 60),
        ];

        $response = $tripay->createCloseTransaction($payload);
        $data = $response['data'] ?? [];

        // Ambil URL pembayaran yang tersedia
        $payUrl = $data['pay_url'] ?? ($data['checkout_url'] ?? ($data['payment_url'] ?? null));

        return [
            'success' => (bool)($response['success'] ?? false),
            'reference' => $data['reference'] ?? null,
            'merchant_ref' => $data['merchant_ref'] ?? $merchantRef,
            'amount' => $data['amount'] ?? $amount,
            'status' => $data['status'] ?? 'UNPAID',
            'pay_url' => $payUrl,
            'raw' => $response,
        ];
    }

    /**
     * Verifikasi signature callback Tripay dari raw body & header signature.
     */
    public function verifyTripayCallback(string $rawBody, ?string $headerSignature): bool
    {
        $tripay = app(\App\Services\TripayService::class);
        return $tripay->verifyCallbackSignature($rawBody, $headerSignature);
    }

    /**
     * Buat transaksi LMS dari PaymentReference Tripay berstatus PAID/SUCCESS
     * - Idempotensi: menggunakan booking_trx_id = merchant_ref
     * - Validasi dasar amount, lanjut meski mismatch kecil (tetap log)
     * - Penautan eksplisit booking_trx_id ke PaymentReference
     */
    public function createCourseTransactionFromTripay(PaymentReference $paymentRef, array $payload = []): ?\App\Models\Transaction
    {
        try {
            $status = strtoupper((string) $paymentRef->status);
            if (!in_array($status, ['PAID', 'SUCCESS'], true)) {
                Log::info('Tripay reference not PAID/SUCCESS, skip transaction creation', [
                    'merchant_ref' => $paymentRef->merchant_ref,
                    'status' => $paymentRef->status,
                ]);
                return null;
            }

            if (!$paymentRef->user_id || !$paymentRef->course_id) {
                Log::warning('Tripay reference missing user_id/course_id, cannot create transaction', [
                    'merchant_ref' => $paymentRef->merchant_ref,
                    'user_id' => $paymentRef->user_id,
                    'course_id' => $paymentRef->course_id,
                ]);
                return null;
            }

            $course = $this->courseRepository->findById((int) $paymentRef->course_id);
            if (!$course) {
                Log::error('Course not found while creating Tripay transaction', [
                    'course_id' => $paymentRef->course_id,
                    'merchant_ref' => $paymentRef->merchant_ref,
                ]);
                return null;
            }

            $adminFee = (int) ($course->admin_fee_amount ?? 0);
            $subTotal = (int) ($course->price ?? 0);
            // Gunakan informasi diskon yang telah dipersist di PaymentReference
            $discountAmount = (int) ($paymentRef->discount_amount ?? 0);
            $paidAmount = (int) ($paymentRef->paid_amount ?? ($payload['paid_amount'] ?? 0));
            if ($paidAmount <= 0) {
                $paidAmount = (int) ($paymentRef->amount ?? ($payload['amount'] ?? 0));
            }

            $expectedGrandTotal = $subTotal + $adminFee - $discountAmount;
            if ($expectedGrandTotal <= 0) {
                $expectedGrandTotal = $subTotal + $adminFee; // safeguard
            }

            if ($paidAmount !== $expectedGrandTotal) {
                Log::warning('Tripay paid amount mismatch against expected total', [
                    'merchant_ref' => $paymentRef->merchant_ref,
                    'paid_amount' => $paidAmount,
                    'expected_total' => $expectedGrandTotal,
                    'sub_total' => $subTotal,
                    'admin_fee' => $adminFee,
                ]);
            }

            $data = [
                'user_id' => (int) $paymentRef->user_id,
                'pricing_id' => null,
                'course_id' => (int) $paymentRef->course_id,
                'sub_total_amount' => $subTotal,
                'admin_fee_amount' => $adminFee,
                'discount_amount' => $discountAmount,
                'discount_id' => $paymentRef->discount_id ? (int) $paymentRef->discount_id : null,
                'grand_total_amount' => $paidAmount > 0 ? $paidAmount : $expectedGrandTotal,
                'payment_type' => 'Tripay',
                'booking_trx_id' => (string) $paymentRef->merchant_ref,
                'is_paid' => true,
                'started_at' => now(),
                'ended_at' => null,
            ];

            $transaction = $this->transactionRepository->firstOrCreateByBookingId($data);
            Log::info('Tripay transaction created/ensured via repository', [
                'transaction_id' => $transaction->id,
                'booking_trx_id' => $transaction->booking_trx_id,
                'merchant_ref' => $paymentRef->merchant_ref,
                'user_id' => $transaction->user_id,
                'course_id' => $transaction->course_id,
                'is_paid' => $transaction->is_paid,
            ]);

            // Increment penggunaan diskon bila ada dan transaksi baru dibuat
            try {
                $recentlyCreated = property_exists($transaction, 'wasRecentlyCreated') ? (bool) $transaction->wasRecentlyCreated : true;
                $discountId = $paymentRef->discount_id ? (int) $paymentRef->discount_id : null;
                if ($discountId && $recentlyCreated) {
                    $discount = $this->discountRepository->findById($discountId);
                    if ($discount) {
                        $this->discountService->useDiscount($discount);
                        Log::info('Discount usage incremented for Tripay transaction', [
                            'discount_id' => $discountId,
                            'discount_code' => $discount->code,
                            'transaction_id' => $transaction->id,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to increment discount usage for Tripay', [
                    'merchant_ref' => $paymentRef->merchant_ref,
                    'error' => $e->getMessage(),
                ]);
            }

            // Attach booking_trx_id ke PaymentReference agar korelasi eksplisit
            try {
                /** @var \App\Repositories\PaymentReferenceRepositoryInterface $paymentRefRepo */
                $paymentRefRepo = app(\App\Repositories\PaymentReferenceRepositoryInterface::class);
                $paymentRefRepo->attachBookingId((string) $paymentRef->merchant_ref, (string) $transaction->booking_trx_id);
            } catch (\Throwable $e) {
                Log::warning('Failed to attach booking_trx_id to PaymentReference', [
                    'merchant_ref' => $paymentRef->merchant_ref,
                    'booking_trx_id' => $transaction->booking_trx_id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $transaction;
        } catch (\Throwable $e) {
            Log::error('Error creating Tripay course transaction', [
                'merchant_ref' => $paymentRef->merchant_ref ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

}
