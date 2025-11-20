<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Transaction;

class StatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Jumlah pengguna terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Total Courses', Course::count())
                ->description('Jumlah kursus tersedia')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            Stat::make('Total Categories', Category::count())
                ->description('Jumlah kategori kursus')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),
            Stat::make('Total Transactions', Transaction::count())
                ->description('Jumlah transaksi')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),
        ];
    }
}