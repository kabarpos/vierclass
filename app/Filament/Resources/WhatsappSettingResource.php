<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;

use App\Filament\Resources\WhatsappSettingResource\Pages;
use App\Models\WhatsappSetting;
use App\Services\DripsenderService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\WhatsappSettingRepositoryInterface;

class WhatsappSettingResource extends Resource
{
    protected static ?string $model = WhatsappSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static ?string $navigationLabel = 'WhatsApp';
    
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'whatsapp-settings';
    }

    protected static ?string $modelLabel = 'Pengaturan WhatsApp';

    protected static ?string $pluralModelLabel = 'Pengaturan WhatsApp';

    protected static string | \UnitEnum | null $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('api_key')
                    ->label('API Key Dripsender')
                    ->required()
                    ->password()
                    ->helperText('Dapatkan API key dari dashboard Dripsender.id')
                    ->columnSpan(2),

                TextInput::make('base_url')
                    ->label('Base URL')
                    ->default('https://api.dripsender.id')
                    ->required()
                    ->url()
                    ->helperText('URL dasar untuk API Dripsender')
                    ->columnSpan(2),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(false)
                    ->helperText('Aktifkan layanan WhatsApp')
                    ->columnSpan(2),

                TextInput::make('webhook_url')
                    ->label('Webhook URL')
                    ->url()
                    ->helperText('URL webhook untuk menerima callback (opsional)')
                    ->columnSpan(2),

                KeyValue::make('additional_settings')
                    ->label('Pengaturan Tambahan')
                    ->keyLabel('Kunci')
                    ->valueLabel('Nilai')
                    ->helperText('Pengaturan tambahan dalam format key-value')
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('api_key')
                    ->label('API Key')
                    ->formatStateUsing(fn (string $state): string => str_repeat('*', 8) . substr($state, -4))
                    ->searchable(),

                TextColumn::make('base_url')
                    ->label('Base URL')
                    ->limit(30),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('test_connection')
                    ->label('Test Koneksi')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (WhatsappSetting $record) {
                        try {
                            // Temporarily set this record as active for testing
                            $originalActive = WhatsappSetting::getActive();
                            if ($originalActive && $originalActive->id !== $record->id) {
                                $originalActive->update(['is_active' => false]);
                            }
                            
                            $record->update(['is_active' => true]);
                            
                            // Test connection
                            $dripsenderService = new DripsenderService();
                            $result = $dripsenderService->testConnection();
                            
                            // Restore original active state
                            if ($originalActive && $originalActive->id !== $record->id) {
                                $record->update(['is_active' => false]);
                                $originalActive->update(['is_active' => true]);
                            }

                            if ($result['success']) {
                                Notification::make()
                                    ->title('Koneksi Berhasil')
                                    ->body($result['message'] ?? 'WhatsApp service terhubung dengan baik')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Koneksi Gagal')
                                    ->body($result['message'] ?? 'Gagal terhubung ke WhatsApp service')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappSettings::route('/'),
            'create' => Pages\CreateWhatsappSetting::route('/create'),
            'edit' => Pages\EditWhatsappSetting::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return app(WhatsappSettingRepositoryInterface::class)->filamentTableQuery();
    }
}
