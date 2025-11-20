<?php

namespace App\Services;

use App\Repositories\RevenueRepositoryInterface;

class ReportingService
{
    public function __construct(
        protected RevenueRepositoryInterface $revenueRepository,
    ) {
    }

    /**
     * Dapatkan ringkasan pendapatan mentor berdasarkan filter.
     *
     * @param array{mentor_id?: int, course_id?: int, from?: string, to?: string} $filters
     * @return array{gross_total: float, admin_fee_total: float, discount_total: float, net_total: float, transactions_count: int}
     */
    public function getMentorRevenueSummary(array $filters = []): array
    {
        return $this->revenueRepository->summarize($filters);
    }
}

