<?php

namespace App\Filament\Widgets;

use App\Services\ReportingService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MentorRevenueOverview extends BaseWidget
{
    protected ?string $heading = 'Ringkasan Pendapatan';

    protected function getStats(): array
    {
        /** @var ReportingService $service */
        $service = app(ReportingService::class);
        // Ringkasan ringan dan konsisten: gunakan base query untuk pengguna saat ini
        // tanpa bergantung pada query string atau filter custom.
        $summary = $service->getMentorRevenueSummary([]);

        $gross = (float) ($summary['gross_total'] ?? 0);
        $admin = (float) ($summary['admin_fee_total'] ?? 0);
        $discount = (float) ($summary['discount_total'] ?? 0);
        $net = (float) ($summary['net_total'] ?? 0);
        $count = (int) ($summary['transactions_count'] ?? 0);

        $fmt = fn (float $v) => 'Rp ' . number_format($v, 0, '', '.');

        return [
            Stat::make('Total Kotor', $fmt($gross))
                ->description('Pendapatan sebelum biaya dan diskon')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Biaya Admin', $fmt($admin))
                ->description('Akumulasi biaya admin')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),

            Stat::make('Total Diskon', $fmt($discount))
                ->description('Akumulasi diskon')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make('Total Bersih', $fmt($net))
                ->description('Pendapatan setelah biaya & diskon')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Jumlah Transaksi', (string) $count)
                ->description('Transaksi yang berhasil dibayar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
        ];
    }
}
