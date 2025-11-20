<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;
use App\Filament\Resources\MidtransSettingResource\Pages;
use App\Models\MidtransSetting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\MidtransSettingRepositoryInterface;

class MidtransSettingResource extends Resource
{
    protected static ?string $model = MidtransSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = null;
    
    protected static ?string $navigationLabel = 'Pengaturan Midtrans';
    
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'midtrans-settings';
    }
    
    protected static ?string $modelLabel = 'Midtrans Setting';
    
    protected static ?string $pluralModelLabel = 'Midtrans Settings';
    
    protected static string | \UnitEnum | null $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('server_key')
                    ->label('Server Key')
                    ->helperText('Enter your Midtrans Server Key (e.g., SB-Mid-server-xxx for sandbox)')
                    ->placeholder('SB-Mid-server-xxxxxxxxxxxxxxxxxxxx')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                    
                TextInput::make('client_key')
                    ->label('Client Key')
                    ->helperText('Enter your Midtrans Client Key (e.g., SB-Mid-client-xxx for sandbox)')
                    ->placeholder('SB-Mid-client-xxxxxxxxxxxxxxx')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                    
                TextInput::make('merchant_id')
                    ->label('Merchant ID')
                    ->helperText('Your Midtrans Merchant ID (e.g., G123456789)')
                    ->placeholder('G123456789')
                    ->maxLength(255)
                    ->columnSpan(2),
                    
                Toggle::make('is_production')
                    ->label('Production Mode')
                    ->helperText('Enable for live transactions')
                    ->columnSpan(1),
                    
                Toggle::make('is_sanitized')
                    ->label('Input Sanitization')
                    ->helperText('Enable input sanitization for security')
                    ->default(true)
                    ->columnSpan(1),
                    
                Toggle::make('is_3ds')
                    ->label('3D Secure')
                    ->helperText('Enable 3D Secure authentication')
                    ->default(true)
                    ->columnSpan(1),
                    
                Toggle::make('is_active')
                    ->label('Active Configuration')
                    ->helperText('Only one configuration can be active at a time')
                    ->default(true)
                    ->columnSpan(1),
                    
                Textarea::make('notes')
                    ->label('Admin Notes')
                    ->helperText('Optional notes about this configuration')
                    ->maxLength(1000)
                    ->rows(3)
                    ->columnSpan(2),
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
                    
                TextColumn::make('masked_server_key')
                    ->label('Server Key')
                    ->fontFamily('mono'),
                    
                TextColumn::make('masked_client_key')
                    ->label('Client Key')
                    ->fontFamily('mono'),
                    
                TextColumn::make('merchant_id')
                    ->label('Merchant ID')
                    ->fontFamily('mono')
                    ->placeholder('Not Set'),
                    
                TextColumn::make('environment_text')
                    ->label('Environment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Production' => 'danger',
                        'Sandbox' => 'success',
                        default => 'gray',
                    }),
                    
                IconColumn::make('is_sanitized')
                    ->label('Sanitized')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                IconColumn::make('is_3ds')
                    ->label('3DS')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                    
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMidtransSettings::route('/'),
            'create' => Pages\CreateMidtransSetting::route('/create'),
            'edit' => Pages\EditMidtransSetting::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return app(MidtransSettingRepositoryInterface::class)->filamentTableQuery();
    }
}
