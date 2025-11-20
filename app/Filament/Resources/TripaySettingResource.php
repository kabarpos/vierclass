<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\TripaySettingResource\Pages;
use App\Models\TripaySetting;
use App\Repositories\TripaySettingRepositoryInterface;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TripaySettingResource extends Resource
{
    protected static ?string $model = TripaySetting::class;

    protected static string | \BackedEnum | null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Pengaturan Tripay';
    protected static ?string $modelLabel = 'Tripay Setting';
    protected static ?string $pluralModelLabel = 'Tripay Settings';
    protected static string | \UnitEnum | null $navigationGroup = 'System';
    protected static ?int $navigationSort = 2;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'tripay-settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('merchant_code')
                    ->label('Merchant Code')
                    ->placeholder('Contoh: T0000')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('api_key')
                    ->label('API Key')
                    ->placeholder('Masukkan API Key')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Textarea::make('private_key')
                    ->label('Private Key')
                    ->placeholder('Masukkan Private Key')
                    ->rows(3)
                    ->required()
                    ->columnSpan(2),

                Toggle::make('is_production')
                    ->label('Production Mode')
                    ->helperText('Aktifkan untuk transaksi live')
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('Active Configuration')
                    ->helperText('Hanya satu konfigurasi yang aktif pada satu waktu')
                    ->default(true)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('masked_api_key')
                    ->label('API Key')
                    ->fontFamily('mono'),

                TextColumn::make('masked_private_key')
                    ->label('Private Key')
                    ->fontFamily('mono'),

                TextColumn::make('environment_text')
                    ->label('Environment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Production' => 'danger',
                        'Sandbox' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('merchant_code')
                    ->label('Merchant Code')
                    ->fontFamily('mono'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTripaySettings::route('/'),
            'create' => Pages\CreateTripaySetting::route('/create'),
            'edit' => Pages\EditTripaySetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var TripaySettingRepositoryInterface $repo */
        $repo = app(TripaySettingRepositoryInterface::class);
        return $repo->filamentTableQuery();
    }
}
