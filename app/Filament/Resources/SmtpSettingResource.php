<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\SmtpSettingRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;

use App\Filament\Resources\SmtpSettingResource\Pages;
use App\Models\SmtpSetting;
use App\Services\MailketingService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class SmtpSettingResource extends Resource
{
    protected static ?string $model = SmtpSetting::class;

    protected static ?string $navigationLabel = 'SMTP Email';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'smtp-settings';
    }

    protected static ?string $modelLabel = 'Pengaturan SMTP';

    protected static ?string $pluralModelLabel = 'Pengaturan SMTP';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('host')
                    ->label('Host')
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('port')
                    ->label('Port')
                    ->numeric()
                    ->default(587)
                    ->required()
                    ->helperText('Umumnya 587 untuk TLS')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                Select::make('encryption')
                    ->label('Enkripsi')
                    ->options([
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                    ])
                    ->default('tls')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('from_name')
                    ->label('From Name')
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('from_email')
                    ->label('From Email')
                    ->email()
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(false)
                    ->helperText('Aktifkan layanan SMTP ini')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('api_endpoint')
                    ->label('API Endpoint (Mailketing)')
                    ->helperText('Opsional, endpoint API Mailketing jika digunakan')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('api_login')
                    ->label('API Login (Mailketing)')
                    ->email()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('api_token')
                    ->label('API Token (Mailketing)')
                    ->password()
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                KeyValue::make('additional_settings')
                    ->label('Pengaturan Tambahan')
                    ->keyLabel('Kunci')
                    ->valueLabel('Nilai')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),
            ])
            ->columns([
                'default' => 1,
                'sm' => 2,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('host')->label('Host')->searchable(),
                TextColumn::make('port')->label('Port'),
                TextColumn::make('from_email')->label('From Email')->searchable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([])
            ->actions([
                Action::make('test')
                    ->label('Uji Koneksi')
                    ->action(function (SmtpSetting $record) {
                        // Temporarily set this record as active to test
                        $original = SmtpSetting::getActive();
                        if ($original && $original->id !== $record->id) {
                            $original->update(['is_active' => false]);
                        }
                        $record->update(['is_active' => true]);

                        try {
                            $service = app(MailketingService::class);
                            $result = $service->sendTest();

                            // Restore original
                            if ($original && $original->id !== $record->id) {
                                $record->update(['is_active' => false]);
                                $original->update(['is_active' => true]);
                            }

                            if ($result['success']) {
                                Notification::make()
                                    ->title('✅ Koneksi Berhasil!')
                                    ->body($result['message'] ?? 'SMTP terhubung dan email tes terkirim')
                                    ->success()
                                    ->duration(5000)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('❌ Koneksi Gagal')
                                    ->body($result['message'] ?? 'Tidak dapat terhubung ke SMTP. Periksa data Anda.')
                                    ->danger()
                                    ->duration(8000)
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('⚠️ Error Koneksi')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->duration(8000)
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
            'index' => Pages\ListSmtpSettings::route('/'),
            'create' => Pages\CreateSmtpSetting::route('/create'),
            'edit' => Pages\EditSmtpSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SmtpSettingRepositoryInterface::class)->filamentTableQuery();
    }
}
