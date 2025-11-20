<?php

namespace App\Filament\Pages\Dashboard;

use Filament\Pages\Page;
use App\Filament\Widgets\MonthlyRevenueChart;
use App\Filament\Widgets\SalesPerCategoryChart;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Facades\Filament;

class Statistik extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static \UnitEnum | string | null $navigationGroup = 'General';

    protected static ?int $navigationSort = 11;

    protected static ?string $title = 'Statistik';

    protected static ?string $navigationLabel = 'Statistik';

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return null;
    }

    // Gunakan properti bawaan untuk title/slug agar konsisten dengan navigasi

    protected static ?string $slug = 'statistics';

    public function getHeaderWidgets(): array
    {
        return [
            MonthlyRevenueChart::class,
            SalesPerCategoryChart::class,
        ];
    }

    /**
     * Tambahkan aksi filter global (mentor, course, tanggal) di header halaman.
     * Aksi ini akan mengarahkan ulang ke halaman yang sama dengan query string.
     */
    public function getHeaderActions(): array
    {
        return [
            Action::make('filter_global')
                ->label('Filter')
                ->icon('heroicon-o-funnel')
                ->color('primary')
                ->form([
                    Select::make('mentor_id')
                        ->label('Mentor')
                        ->options(fn () => \App\Models\User::role('mentor')->pluck('name', 'id')->toArray())
                        ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                        ->default(request()->integer('mentor_id') ?: null)
                        ->extraAttributes(['class' => 'cursor-pointer']),
                    Select::make('course_id')
                        ->label('Course')
                        ->options(fn () => \App\Models\Course::orderBy('name')->pluck('name', 'id')->toArray())
                        ->default(request()->integer('course_id') ?: null)
                        ->extraAttributes(['class' => 'cursor-pointer']),
                    DatePicker::make('from')
                        ->label('Dari')
                        ->default(request()->query('from') ?? request()->query('from_date'))
                        ->extraAttributes(['class' => 'cursor-pointer']),
                    DatePicker::make('to')
                        ->label('Sampai')
                        ->default(request()->query('to') ?? request()->query('to_date'))
                        ->extraAttributes(['class' => 'cursor-pointer']),
                ])
                ->action(function (array $data) {
                    $params = array_filter([
                        'mentor_id' => $data['mentor_id'] ?? null,
                        'course_id' => $data['course_id'] ?? null,
                        'from' => $data['from'] ?? null,
                        'to' => $data['to'] ?? null,
                    ], fn ($v) => filled($v));

                    $panelPath = Filament::getCurrentPanel()->getPath();
                    $base = url('/' . $panelPath . '/' . static::$slug);
                    $url = $base . (empty($params) ? '' : ('?' . http_build_query($params)));
                    $this->redirect($url);
                })
                ->extraAttributes(['class' => 'cursor-pointer']),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }
}
