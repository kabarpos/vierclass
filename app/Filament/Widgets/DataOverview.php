<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;

class DataOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $totalTransactions = Transaction::where('is_paid', true)->count();
        $totalRevenue = Transaction::where('is_paid', true)->sum('grand_total_amount');
        $totalCoursesSold = Transaction::where('is_paid', true)->whereNotNull('course_id')->count();
        $averageOrderValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        return [
            Stat::make('Total Transaksi', number_format($totalTransactions))
                ->description('Transaksi yang berhasil')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Pendapatan kotor')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            
            Stat::make('Course Terjual', number_format($totalCoursesSold))
                ->description('Jumlah course terjual')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            
            Stat::make('Rata-rata Nilai Order', 'Rp ' . number_format($averageOrderValue, 0, ',', '.'))
                ->description('AOV per transaksi')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
        ];
    }
}