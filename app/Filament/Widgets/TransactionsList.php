<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class TransactionsList extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Transaksi Terbaru';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with(['student', 'course', 'discount'])
                    ->where('is_paid', true)
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('booking_trx_id')
                    ->label('ID Transaksi')
                    ->searchable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('student.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('course.name')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->formatStateUsing(fn ($state) => $state > 0 ? 'Rp ' . number_format($state, 0, '', '.') : '-')
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('grand_total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                BadgeColumn::make('payment_type')
                    ->label('Metode Pembayaran')
                    ->colors([
                        'primary' => 'manual_transfer',
                        'success' => 'credit_card',
                    ])
                    ->extraAttributes(['class' => 'cursor-pointer']),
                    
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}