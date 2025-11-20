<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Repositories\RevenueRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyRevenueChart extends LineChartWidget
{
    protected ?string $heading = 'Pendapatan Bulanan';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        /** @var RevenueRepositoryInterface $repo */
        $repo = app(RevenueRepositoryInterface::class);

        // Baca filter dari query string (fallback mendukung from_date/to_date)
        $fromReq = request()->query('from') ?? request()->query('from_date');
        $toReq = request()->query('to') ?? request()->query('to_date');

        // Tentukan rentang bulan untuk label:
        // - Jika ada filter from/to, gunakan rentang tersebut (diselaraskan ke bulan)
        // - Jika tidak ada, gunakan 12 bulan terakhir
        if ($fromReq && $toReq) {
            $start = Carbon::parse($fromReq)->startOfMonth();
            $end = Carbon::parse($toReq)->endOfMonth();
        } else {
            $start = Carbon::now()->subMonths(11)->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        $months = collect([]);
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $months->push($cursor->format('Y-m'));
            $cursor->addMonth()->startOfMonth();
        }

        // Terapkan repository pattern dan filter berbasis started_at
        $filters = [
            'mentor_id' => request()->integer('mentor_id') ?: null,
            'course_id' => request()->integer('course_id') ?: null,
            'from' => $fromReq ?: $start->toDateString(),
            'to' => $toReq ?: $end->toDateString(),
        ];

        $query = $repo->applyFilters($repo->baseQueryForCurrentUser(), $filters);
        // Gunakan query builder dasar untuk agregasi, grup per bulan berdasarkan started_at
        $rows = $query->toBase()
            ->select([
                DB::raw("DATE_FORMAT(started_at, '%Y-%m') as ym"),
                DB::raw('SUM(grand_total_amount) as total'),
            ])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = $months->map(fn ($ym) => Carbon::createFromFormat('Y-m', $ym)->locale('id')->translatedFormat('M Y'));
        $data = $months->map(fn ($ym) => (float) ($rows[$ym]->total ?? 0));

        return [
            'labels' => $labels->toArray(),
            'datasets' => [
                [
                    'label' => 'Pendapatan (IDR)',
                    'data' => $data->toArray(),
                    // Gunakan brand color dari app.css (mountain-meadow 500)
                    'borderColor' => '#18b18a',
                    'backgroundColor' => 'rgba(24, 177, 138, 0.2)',
                    'tension' => 0.3,
                ],
            ],
        ];
    }
}
