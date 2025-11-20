<?php

namespace App\Filament\Widgets;

use App\Repositories\RevenueRepositoryInterface;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;

class MentorRevenueTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Pendapatan per Transaksi';

    public function table(Table $table): Table
    {
        /** @var RevenueRepositoryInterface $repo */
        $repo = app(RevenueRepositoryInterface::class);

        return $table
            // Query dasar ringan; filter akan diterapkan oleh Filter bawaan tabel
            ->query(function () use ($repo) {
                return $repo->baseQueryForCurrentUser()->latest('started_at');
            })
            ->persistFiltersInSession()
            ->columns([
                TextColumn::make('student.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->extraAttributes(['class' => 'cursor-pointer whitespace-normal break-words']),

                TextColumn::make('course.name')
                    ->label('Course')
                    ->searchable()
                    ->extraAttributes(['class' => 'cursor-pointer whitespace-normal break-words']),

                TextColumn::make('grand_total_amount')
                    ->label('Total Kotor')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Kotor'))
                    ->extraAttributes(['class' => 'cursor-pointer']),

                TextColumn::make('admin_fee_amount')
                    ->label('Biaya Admin')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Biaya Admin'))
                    ->extraAttributes(['class' => 'cursor-pointer']),

                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Diskon'))
                    ->extraAttributes(['class' => 'cursor-pointer']),

                TextColumn::make('net_total')
                    ->label('Pendapatan Bersih')
                    ->getStateUsing(function ($record) {
                        $gross = (float) ($record->grand_total_amount ?? 0);
                        $admin = (float) ($record->admin_fee_amount ?? 0);
                        $discount = (float) ($record->discount_amount ?? 0);
                        return $gross - $admin - $discount;
                    })
                    ->money('IDR')
                    // Kolom ini bukan kolom fisik di DB, jadi sorting harus custom memakai ekspresi SQL
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $dir = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'asc';
                        return $query->orderByRaw('(grand_total_amount - admin_fee_amount - COALESCE(discount_amount, 0)) ' . $dir);
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Bersih')
                            ->using(function (QueryBuilder $query) {
                                // Hitung total bersih melalui ekspresi SQL agar akurat
                                return (float) $query->sum(DB::raw('(grand_total_amount - admin_fee_amount - COALESCE(discount_amount, 0))'));
                            })
                    )
                    ->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->headerActions([
                Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn () => '/api/admin/transactions/export?format=csv' . $this->buildCsvQuerySuffix())
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->filters([
                // Aktifkan Filter bawaan tabel (lebih ringan dan native Filament 4)
                SelectFilter::make('mentor_id')
                    ->label('Mentor')
                    ->options(function () {
                        return \App\Models\User::role('mentor')->pluck('name', 'id')->toArray();
                    })
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                    ->query(function (Builder $query, $value) {
                        if (!$value) return $query;
                        return $query->whereHas('course.courseMentors', function (Builder $q) use ($value) {
                            $q->where('user_id', (int) $value);
                        });
                    }),

                SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(function () {
                        return \App\Models\Course::orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->visible(fn () => true)
                    ->query(function (Builder $query, $value) {
                        if (!$value) return $query;
                        return $query->where('course_id', (int) $value);
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('to')->label('Sampai'),
                    ])
                    ->visible(fn () => true)
                    ->query(function (Builder $query, array $data) {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;

                        $fromDT = $from ? Carbon::parse($from)->startOfDay() : null;
                        $toDT = $to ? Carbon::parse($to)->endOfDay() : null;

                        if ($fromDT && $toDT) {
                            return $query->whereBetween('started_at', [$fromDT, $toDT]);
                        }
                        if ($fromDT) {
                            return $query->where('started_at', '>=', $fromDT);
                        }
                        if ($toDT) {
                            return $query->where('started_at', '<=', $toDT);
                        }
                        return $query;
                    }),
            ]);
    }

    protected function buildCsvQuerySuffix(): string
    {
        // Baca state filter aktif dari Form filter tabel (native Filament)
        $state = method_exists($this, 'getTableFiltersForm')
            ? ($this->getTableFiltersForm()->getState() ?? [])
            : [];

        $courseId = $state['course_id'] ?? null;
        $mentorId = $state['mentor_id'] ?? null; // Saat ini endpoint ekspor tidak mendukung mentor_id
        $dateRange = $state['date_range'] ?? [];
        $from = is_array($dateRange) ? ($dateRange['from'] ?? null) : null;
        $to = is_array($dateRange) ? ($dateRange['to'] ?? null) : null;

        // Mapping ke parameter yang didukung endpoint ekspor
        $mapped = [];
        if (!empty($courseId)) {
            $mapped['course_id'] = (int) $courseId;
        }
        if (!empty($from)) {
            $mapped['from_date'] = $from;
        }
        if (!empty($to)) {
            $mapped['to_date'] = $to;
        }

        return empty($mapped) ? '' : ('&' . http_build_query($mapped));
    }
}
