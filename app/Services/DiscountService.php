<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Course;
use App\Repositories\DiscountRepositoryInterface;
use App\Repositories\CourseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DiscountService
{
    protected $discountRepository;
    protected $courseRepository;

    public function __construct(DiscountRepositoryInterface $discountRepository, CourseRepositoryInterface $courseRepository)
    {
        $this->discountRepository = $discountRepository;
        $this->courseRepository = $courseRepository;
    }
    /**
     * Create a new discount
     */
    public function createDiscount(array $data): Discount
    {
        // Validasi awal: jika kode diinput manual dan sudah ada, beri feedback jelas
        if (!empty($data['code']) && $this->discountRepository->existsByCode($data['code'])) {
            throw new \InvalidArgumentException('Kode diskon sudah digunakan, silakan pilih kode lain.');
        }

        $maxAttempts = 5;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                if (empty($data['code'])) {
                    $data['code'] = $this->generateUniqueCode('DISC');
                }
                return $this->discountRepository->create($data);
            } catch (QueryException $e) {
                // Deteksi pelanggaran unique constraint (lintas driver)
                $sqlState = $e->getCode();
                $driverCode = $e->errorInfo[1] ?? null;
                $isUniqueViolation = in_array($sqlState, ['23000', '23505'], true)
                    || in_array($driverCode, [1062, 19], true)
                    || str_contains(strtolower($e->getMessage()), 'unique');

                if ($isUniqueViolation) {
                    Log::warning('Collision kode diskon saat createDiscount, mencoba ulang', [
                        'attempt' => $attempt,
                        'code' => $data['code'] ?? null,
                    ]);

                    // Jika kode disediakan manual, bubble up agar caller aware
                    if (!empty($data['code']) && $this->discountRepository->existsByCode($data['code'])) {
                        throw $e;
                    }

                    // Reset agar di-generate ulang pada iterasi berikutnya
                    $data['code'] = null;
                    continue;
                }

                // Error lain: langsung bubble up
                throw $e;
            }
        }

        throw new \RuntimeException('Gagal membuat diskon: terjadi bentrok kode berulang. Silakan coba lagi.');
    }
    
    /**
     * Update discount
     */
    public function updateDiscount(Discount $discount, array $data): Discount
    {
        return $this->discountRepository->update($discount, $data);
    }
    
    /**
     * Delete discount
     */
    public function deleteDiscount(Discount $discount): bool
    {
        return $this->discountRepository->delete($discount);
    }
    
    /**
     * Get all active discounts
     */
    public function getActiveDiscounts(): Collection
    {
        return $this->discountRepository->getActive();
    }
    
    /**
     * Find discount by code
     */
    public function findByCode(string $code): ?Discount
    {
        return $this->discountRepository->findByCode($code);
    }
    
    /**
     * Validate discount for course
     */
    public function validateDiscountForCourse(string $discountCode, Course $course): array
    {
        $discount = $this->findByCode($discountCode);
        
        if (!$discount) {
            return [
                'valid' => false,
                'message' => 'Kode diskon tidak valid atau sudah tidak aktif.',
                'discount' => null
            ];
        }
        
        if (!$discount->isValid($course->price)) {
            return [
                'valid' => false,
                'message' => $this->getInvalidDiscountMessage($discount, $course),
                'discount' => $discount
            ];
        }
        
        $discountAmount = $discount->calculateDiscount($course->price);
        $finalPrice = max(0, $course->price - $discountAmount);
        
        return [
            'valid' => true,
            'message' => 'Kode diskon berhasil diterapkan!',
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'savings' => $course->price - $finalPrice
        ];
    }
    
    /**
     * Apply discount to course
     */
    public function applyDiscountToCourse(Course $course, string $discountCode): array
    {
        $validation = $this->validateDiscountForCourse($discountCode, $course);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        return [
            'success' => true,
            'original_price' => $course->price,
            'discount_amount' => $validation['discount_amount'],
            'final_price' => $validation['final_price'],
            'discount' => $validation['discount'],
            'savings' => $validation['savings']
        ];
    }
    
    /**
     * Use discount (increment usage count)
     */
    public function useDiscount(Discount $discount): bool
    {
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            return false;
        }
        
        $this->discountRepository->incrementUsage($discount);
        return true;
    }
    
    /**
     * Get discount statistics
     */
    public function getDiscountStatistics(Discount $discount): array
    {
        return [
            'total_usage' => $discount->used_count,
            'remaining_usage' => $discount->usage_limit ? max(0, $discount->usage_limit - $discount->used_count) : null,
            'usage_percentage' => $discount->usage_limit ? round(($discount->used_count / $discount->usage_limit) * 100, 2) : 0,
            'is_expired' => $discount->end_date ? Carbon::now()->gt($discount->end_date) : false,
            'days_remaining' => $discount->end_date ? max(0, Carbon::now()->diffInDays($discount->end_date, false)) : null
        ];
    }
    
    /**
     * Get courses with active discounts
     */
    public function getCoursesWithDiscounts(): Collection
    {
        return $this->courseRepository->getCoursesWithDiscounts();
    }
    
    /**
     * Generate unique discount code
     */
    public function generateUniqueCode(string $prefix = 'DISC'): string
    {
        do {
            $code = $prefix . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        } while ($this->discountRepository->existsByCode($code));
        
        return $code;
    }
    
    /**
     * Get invalid discount message
     */
    private function getInvalidDiscountMessage(Discount $discount, Course $course): string
    {
        $reasons = [];
        
        if ($discount->minimum_amount && $course->price < $discount->minimum_amount) {
            $reasons[] = 'Minimum pembelian Rp ' . number_format($discount->minimum_amount, 0, '', '.');
        }
        
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            $reasons[] = 'Kuota penggunaan sudah habis';
        }
        
        if ($discount->start_date && now() < $discount->start_date) {
            $reasons[] = 'Diskon belum berlaku';
        }
        
        if ($discount->end_date && now() > $discount->end_date) {
            $reasons[] = 'Diskon sudah berakhir';
        }
        
        return 'Kode diskon tidak dapat digunakan: ' . implode(', ', $reasons);
    }
}
