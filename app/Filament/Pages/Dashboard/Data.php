<?php

namespace App\Filament\Pages\Dashboard;

use Filament\Pages\Page;
use App\Filament\Widgets\DataOverview;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TransactionsList;
use App\Filament\Widgets\TopCourses;

class Data extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static \UnitEnum | string | null $navigationGroup = 'General';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Data';

    protected static ?string $navigationLabel = 'Data';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'data';
    }

    public function getTitle(): string
    {
        return 'Data';
    }

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            DataOverview::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            TransactionsList::class,
            TopCourses::class,
        ];
    }

    public function getHeaderWidgetsColumns(): array | int
    {
        return 1;
    }

    public function getFooterWidgetsColumns(): array | int
    {
        return [
            'sm' => 1,
            'md' => 2,
        ];
    }
}