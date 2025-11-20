<?php

namespace App\Repositories;

use App\Models\Discount;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CourseRepositoryInterface
{
    public function searchByKeyword(string $keyword): Collection;

    public function getAllWithCategory(): Collection;

    public function getFeaturedCourses(int $limit = 6): Collection;

    public function getCoursesWithFilters(array $filters = [], ?string $sort = null, int $perPage = 12): LengthAwarePaginator;
    
    public function getActiveDiscounts(): Collection;
    
    public function findDiscountByCode(string $code): ?Discount;
    
    public function getCoursesWithDiscounts(): Collection;

    public function findById(int $id): ?Course;

    /**
     * Dapatkan daftar course berbayar lengkap dengan relasi dasar untuk pembelian
     */
    public function getCoursesForPurchase(): Collection;

    /**
     * Dapatkan course yang sudah dibeli user dan kelompokkan per kategori
     */
    public function getPurchasedCoursesGroupedByCategoryForUser(int $userId): Collection;

    /**
     * Dapatkan course beserta relasi yang diperlukan untuk status pembelian/akses
     */
    public function getCourseWithRelations(int $courseId): ?Course;

    /**
     * Query standar untuk tabel Course di Filament Resources.
     */
    public function filamentTableQuery(): Builder;
}
