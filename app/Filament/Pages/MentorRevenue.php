<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MentorRevenue extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = null;
    protected static \UnitEnum | string | null $navigationGroup = 'Products';
    protected static ?int $navigationSort = 14;
    protected static ?string $title = 'Pendapatan Mentor';
    protected static ?string $navigationLabel = 'Pendapatan Mentor';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'mentor-revenue';
    }

    public function getTitle(): string
    {
        return 'Pendapatan Mentor';
    }

    // Widget di bagian header (ringkasan pendapatan)
    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MentorRevenueOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): array | int
    {
        return 1;
    }

    public function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\MentorRevenueTable::class,
        ];
    }

    public function getFooterWidgetsColumns(): array | int
    {
        return 1;
    }
}
