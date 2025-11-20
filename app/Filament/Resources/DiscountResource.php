<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;
use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
// Grid tidak diperlukan di sini karena kita mengikuti layout top-level default
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';
    
    protected static ?string $navigationLabel = 'Diskon';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nama Diskon')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Kode Diskon')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Kode diskon sudah digunakan, silakan pilih kode lain.',
                    ])
                    ->maxLength(255)
                    ->helperText('Kode unik untuk diskon ini'),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->helperText('Deskripsi optional untuk diskon'),

                Select::make('type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percentage' => 'Persentase (%)',
                        'fixed' => 'Nominal Tetap (Rp)',
                    ])
                    ->required()
                    ->reactive()
                    ->helperText('Pilih tipe diskon: persentase atau nominal tetap'),

                TextInput::make('value')
                    ->label('Nilai Diskon')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : 'Rp')
                    ->helperText(fn ($get) => $get('type') === 'percentage' 
                        ? 'Masukkan persentase diskon (contoh: 10 untuk 10%)' 
                        : 'Masukkan nominal diskon dalam Rupiah'),

                TextInput::make('minimum_amount')
                    ->label('Minimum Pembelian (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->helperText('Minimum pembelian untuk menggunakan diskon (kosongkan jika tidak ada)'),

                TextInput::make('maximum_discount')
                    ->label('Maksimal Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->visible(fn ($get) => $get('type') === 'percentage')
                    ->helperText('Maksimal nominal diskon untuk tipe persentase (kosongkan jika tidak ada batas)'),

                // Ikuti layout dua kolom top-level, tanpa nested grid
                DateTimePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->timezone(config('app.timezone'))
                    ->helperText('Tanggal dan waktu mulai berlaku diskon'),

                DateTimePicker::make('end_date')
                    ->label('Tanggal Berakhir')
                    ->required()
                    ->after('start_date')
                    ->timezone(config('app.timezone'))
                    ->helperText('Tanggal dan waktu berakhir diskon'),

                TextInput::make('usage_limit')
                    ->label('Batas Penggunaan')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Maksimal berapa kali diskon bisa digunakan (kosongkan untuk unlimited)'),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->extraAttributes(['class' => 'cursor-pointer'])
                    ->helperText('Aktifkan atau nonaktifkan diskon'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Diskon')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kode diskon berhasil disalin!')
                    ->sortable()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Persentase',
                        'fixed' => 'Nominal Tetap',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->type === 'percentage' 
                            ? $state . '%' 
                            : 'Rp ' . number_format($state, 0, '', '.')
                    )
                    ->sortable(),

                TextColumn::make('usage_stats')
                    ->label('Penggunaan')
                    ->getStateUsing(fn ($record) => 
                        $record->usage_limit 
                            ? $record->used_count . '/' . $record->usage_limit
                            : $record->used_count . '/∞'
                    ),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percentage' => 'Persentase',
                        'fixed' => 'Nominal Tetap',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Standarisasi ke repository untuk query Filament
        $repo = app(\App\Repositories\DiscountRepositoryInterface::class);
        return $repo->filamentTableQuery();
    }
}
