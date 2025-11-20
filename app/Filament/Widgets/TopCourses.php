<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Course;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class TopCourses extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Course Terlaris';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Course::query()
                    // Eager load kategori untuk menghindari N+1 di kolom category.name
                    ->with(['category'])
                    ->withCount(['transactions' => function (Builder $query) {
                        $query->where('is_paid', true);
                    }])
                    ->having('transactions_count', '>', 0)
                    ->orderBy('transactions_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->circular()
                    ->size(50)
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('name')
                    ->label('Nama Course')
                    ->searchable()
                    ->limit(40)
                    ->extraAttributes(['class' => 'cursor-pointer whitespace-normal break-words']),
                    
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('transactions_count')
                    ->label('Terjual')
                    ->sortable()
                    ->suffix(' kali')
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->defaultSort('transactions_count', 'desc')
            ->paginated(false);
    }
}
