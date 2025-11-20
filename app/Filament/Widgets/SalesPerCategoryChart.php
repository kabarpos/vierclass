<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Repositories\RevenueRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SalesPerCategoryChart extends BarChartWidget
{
    protected ?string $heading = 'Penjualan per Kategori';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        /** @var RevenueRepositoryInterface $repo */
        $repo = app(RevenueRepositoryInterface::class);

        // Baca filter dari query string dengan fallback from_date/to_date
        $filters = [
            'mentor_id' => request()->integer('mentor_id') ?: null,
            'course_id' => request()->integer('course_id') ?: null,
            'from' => request()->query('from') ?? request()->query('from_date'),
            'to' => request()->query('to') ?? request()->query('to_date'),
        ];

        // Terapkan repository pattern (akses & filter berbasis started_at)
        $query = $repo->applyFilters($repo->baseQueryForCurrentUser(), $filters);

        // Agregasi jumlah transaksi per kategori
        $rows = $query->toBase()
            ->join('courses', 'transactions.course_id', '=', 'courses.id')
            ->join('categories', 'courses.category_id', '=', 'categories.id')
            ->select('categories.name as category', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.name')
            ->orderBy('total', 'desc')
            ->get();

        $labels = $rows->pluck('category')->toArray();
        $data = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $data,
                    // Gunakan brand color dari app.css (mountain-meadow 500)
                    'backgroundColor' => 'rgba(24, 177, 138, 0.2)',
                    'borderColor' => '#18b18a',
                ],
            ],
        ];
    }
}
