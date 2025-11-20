<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Transaction;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalCourses = Course::count();
        $totalCategories = Category::count();
        $totalTransactions = Transaction::count();
        
        return [
            Stat::make('Total Pengguna', number_format($totalUsers))
                ->description('Jumlah pengguna terdaftar')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            
            Stat::make('Total Kursus', number_format($totalCourses))
                ->description('Jumlah kursus tersedia')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            
            Stat::make('Total Kategori', number_format($totalCategories))
                ->description('Jumlah kategori kursus')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            
            Stat::make('Total Transaksi', number_format($totalTransactions))
                ->description('Jumlah transaksi')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
        ];
    }
}