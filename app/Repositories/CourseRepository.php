<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseRepository implements CourseRepositoryInterface
{
    public function searchByKeyword(string $keyword): Collection
    {
        $cacheKey = 'courses:search:' . md5($keyword);
        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($keyword) {
            return Course::with('category')
                ->withCount(['courseStudents', 'courseSections'])
                ->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('about', 'like', "%{$keyword}%");
                })
                ->get();
        });
    }

    public function getAllWithCategory(): Collection
    {
        return Cache::remember('courses:all_with_category', now()->addMinutes(2), function () {
            return Course::with('category')
                ->withCount(['courseStudents', 'courseSections'])
                ->latest()
                ->get();
        });
    }

    public function getFeaturedCourses(int $limit = 6): Collection
    {
        $cacheKey = 'courses:featured:limit:' . $limit;
        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($limit) {
            return Course::with(['category'])
                ->withCount(['courseStudents', 'courseSections'])
                ->orderBy('course_students_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    public function getCoursesWithFilters(array $filters = [], ?string $sort = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = Course::with(['category', 'courseMentors.mentor'])
            ->withCount(['courseStudents', 'courseSections']);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['price_type'])) {
            if ($filters['price_type'] === 'free') {
                $query->where('price', 0);
            } elseif ($filters['price_type'] === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('about', 'like', "%{$filters['search']}%");
            });
        }

        // Apply sorting
        switch ($sort) {
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('course_students_count', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        return $query->paginate($perPage);
    }
    
    /**
     * Get active discounts
     */
    public function getActiveDiscounts(): Collection
    {
        return Cache::remember('discounts:active_available', now()->addMinutes(5), function () {
            return Discount::active()->available()->get();
        });
    }
    
    /**
     * Find discount by code
     */
    public function findDiscountByCode(string $code): ?Discount
    {
        $key = 'discounts:code:' . strtolower($code);
        return Cache::remember($key, now()->addMinutes(5), function () use ($code) {
            return Discount::where('code', $code)
                ->active()
                ->available()
                ->first();
        });
    }
    
    /**
     * Get courses with discounts applied
     */
    public function getCoursesWithDiscounts(): Collection
    {
        return Cache::remember('courses:with_discounts', now()->addMinutes(3), function () {
            return Course::with(['category'])
                ->withCount(['courseStudents', 'courseSections'])
                ->where('original_price', '>', 0)
                ->whereColumn('original_price', '>', 'price')
                ->get();
        });
    }

    public function findById(int $id): ?Course
    {
        return Course::find($id);
    }

    /**
     * Dapatkan daftar course berbayar lengkap dengan relasi dasar untuk pembelian
     */
    public function getCoursesForPurchase(): Collection
    {
        return Cache::remember('courses:for_purchase', now()->addMinutes(3), function () {
            return Course::where('price', '>', 0)
                ->with(['category', 'courseMentors.mentor'])
                ->withCount(['courseStudents', 'courseSections'])
                ->orderBy('is_popular', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Dapatkan course yang sudah dibeli user dan kelompokkan per kategori
     */
    public function getPurchasedCoursesGroupedByCategoryForUser(int $userId): Collection
    {
        $purchasedCourses = Cache::remember('courses:purchased_grouped:user:' . $userId, now()->addMinutes(2), function () use ($userId) {
            return Course::whereHas('transactions', function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('is_paid', true);
                })
                ->orWhereHas('courseStudents', function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('is_active', true);
                })
                ->with('category')
                ->withCount(['courseStudents', 'courseSections'])
                ->get();
        });

        return $purchasedCourses->groupBy(function ($course) {
            return $course->category->name ?? 'Uncategorized';
        });
    }

    /**
     * Dapatkan course beserta relasi yang diperlukan untuk status pembelian/akses
     */
    public function getCourseWithRelations(int $courseId): ?Course
    {
        return Cache::remember('courses:detail:' . $courseId, now()->addMinute(), function () use ($courseId) {
            return Course::with([
                    'category',
                    'benefits',
                    'courseSections.sectionContents',
                    'courseMentors.mentor'
                ])
                ->find($courseId);
        });
    }

    /**
     * Query standar untuk tabel Course di Filament Resources.
     */
    public function filamentTableQuery(): Builder
    {
        return Course::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['category'])
            ->withCount([
                'transactions as paid_transactions_count' => function ($q) {
                    $q->where('is_paid', true);
                },
            ])
            ->withSum([
                'transactions as paid_revenue_sum' => function ($q) {
                    $q->where('is_paid', true);
                },
            ], 'grand_total_amount');
    }
}
