<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\Discount;
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\DiscountRepositoryInterface;
use App\Services\DiscountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    protected $transactionRepository;
    protected $discountService;
    protected $courseRepository;
    protected $discountRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        DiscountService $discountService,
        CourseRepositoryInterface $courseRepository,
        DiscountRepositoryInterface $discountRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->discountService = $discountService;
        $this->courseRepository = $courseRepository;
        $this->discountRepository = $discountRepository;
    }


    /**
     * Prepare checkout for course purchase
     */
    public function prepareCourseCheckout(Course $course)
    {
        $user = Auth::user();
        $alreadyPurchased = false;

        // Gunakan repository untuk cek pembelian agar konsisten dengan arsitektur
        if ($user) {
            try {
                $alreadyPurchased = $this->transactionRepository->userHasPurchasedCourse((int) $user->id, (int) $course->id);
            } catch (\Throwable $e) {
                Log::warning('Failed to check purchased status via repository', [
                    'user_id' => $user->id ?? null,
                    'course_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
                $alreadyPurchased = false;
            }
        } else {
            Log::warning('prepareCourseCheckout invoked without authenticated user');
        }

        $admin_fee_amount = (float) ($course->admin_fee_amount ?? 0);
        $sub_total_amount = (float) ($course->price);
        
        // Check for applied discount in session
        $appliedDiscount = session()->get('applied_discount');
        $discount_amount = 0;
        
        if ($appliedDiscount) {
            try {
                $discount = $this->discountRepository->findById((int) ($appliedDiscount['id'] ?? 0));
                if ($discount && $discount->isValid($sub_total_amount)) {
                    $discount_amount = $discount->calculateDiscount($sub_total_amount);
                } else {
                    // Remove invalid discount from session
                    session()->forget('applied_discount');
                    session()->forget('discount_amount'); // Hapus juga discount_amount
                    $appliedDiscount = null;
                }
            } catch (\Throwable $e) {
                Log::warning('Discount repository error in prepareCourseCheckout', [
                    'applied_discount' => $appliedDiscount,
                    'error' => $e->getMessage()
                ]);
                session()->forget('applied_discount');
                session()->forget('discount_amount');
                $appliedDiscount = null;
            }
        }
        
        $grand_total_amount = $sub_total_amount - $discount_amount + $admin_fee_amount;

        // For course purchases, no subscription dates needed
        $started_at = now();
        $ended_at = null; // Lifetime access

        // Save the selected course ID and admin fee into the session
        session()->put('course_id', $course->id);
        session()->put('admin_fee_amount', $admin_fee_amount);
        // HAPUS: session()->put('discount_amount', $discount_amount);
        // PaymentService akan menghitung sendiri discount_amount dari applied_discount
        session()->forget('pricing_id'); // Clear any existing pricing session

        Log::info('Prepared course checkout totals', [
            'course_id' => $course->id,
            'admin_fee_amount' => $admin_fee_amount,
            'sub_total_amount' => $sub_total_amount,
            'discount_amount' => $discount_amount,
            'grand_total_amount' => $grand_total_amount,
            'alreadyPurchased' => $alreadyPurchased
        ]);

        return compact(
            'admin_fee_amount',
            'grand_total_amount',
            'sub_total_amount',
            'discount_amount',
            'course',
            'user',
            'alreadyPurchased',
            'started_at',
            'ended_at',
            'appliedDiscount'
        );
    }

    public function getRecentCourse()
    {
        $courseId = (int) session()->get('course_id');
        return $this->courseRepository->findById($courseId);
    }

    public function getUserTransactions()
    {
        $user = Auth::user();

        return $this->transactionRepository->getUserTransactions($user->id);
    }
    

    
    /**
     * Check if user has purchased a course
     */
    public function hasUserPurchasedCourse($courseId)
    {
        $user = Auth::user();
        return $this->transactionRepository->userHasPurchasedCourse((int) $user->id, (int) $courseId);
    }
    
    /**
     * Apply discount to session
     */
    public function applyDiscount(Discount $discount)
    {
        session()->put('applied_discount', [
            'id' => $discount->id,
            'code' => $discount->code,
            'name' => $discount->name,
            'type' => $discount->type,
            'value' => $discount->value,
            'maximum_discount' => $discount->maximum_discount,
            'applied_at' => now()->toISOString()
        ]);
        
        // Log untuk debugging
        Log::info('Discount applied to session', [
            'discount_id' => $discount->id,
            'discount_code' => $discount->code,
            'discount_type' => $discount->type,
            'discount_value' => $discount->value,
            'discount_maximum_discount' => $discount->maximum_discount
        ]);
    }
    
    /**
     * Remove discount from session
     */
    public function removeDiscount()
    {
        // Hapus semua session yang terkait dengan diskon
        session()->forget('applied_discount');
        session()->forget('discount_amount');
        
        // Log untuk debugging
        Log::info('Discount removed from session', [
            'remaining_session_keys' => array_keys(session()->all())
        ]);
    }
    
    /**
     * Get applied discount from session
     */
    public function getAppliedDiscount()
    {
        return session()->get('applied_discount');
    }
    
    /**
     * Calculate pricing with discount for course
     */
    public function calculatePricingWithDiscount(Course $course, Discount $discount = null)
    {
        $subtotal = $course->price;
        $admin_fee = $course->admin_fee_amount ?? 0;
        $discount_amount = 0;
        
        if ($discount && $discount->isValid($subtotal)) {
            $discount_amount = $discount->calculateDiscount($subtotal);
        }
        
        $grand_total = $subtotal - $discount_amount + $admin_fee;
        
        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discount_amount,
            'admin_fee' => $admin_fee,
            'grand_total' => $grand_total,
            'savings' => $discount_amount
        ];
    }
}
