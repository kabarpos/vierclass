<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\WhatsappMessageTemplateResource\Pages;
use App\Models\WhatsappMessageTemplate;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\WhatsappMessageTemplateRepositoryInterface;

class WhatsappMessageTemplateResource extends Resource
{
    protected static ?string $model = WhatsappMessageTemplate::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static ?string $navigationLabel = 'Template WhatsApp';
    
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'whatsapp-message-templates';
    }

    protected static ?string $modelLabel = 'Template Pesan WhatsApp';

    protected static ?string $pluralModelLabel = 'Template Pesan WhatsApp';

    protected static string | \UnitEnum | null $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Template')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Select::make('type')
                    ->label('Tipe Template')
                    ->required()
                    ->options(WhatsappMessageTemplate::getAvailableTypes())
                    ->helperText('Pilih tipe template untuk menentukan variabel yang tersedia')
                    ->columnSpan(2),

                TextInput::make('subject')
                    ->label('Subject')
                    ->maxLength(255)
                    ->helperText('Subject template (opsional)')
                    ->columnSpan(2),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->helperText('Hanya template aktif yang akan digunakan')
                    ->columnSpan(2),

                Textarea::make('message')
                    ->label('Isi Pesan')
                    ->required()
                    ->rows(8)
                    ->helperText('Gunakan variabel seperti {user_name}, {order_id}, dll. sesuai tipe template')
                    ->columnSpan(2),

                KeyValue::make('variables')
                    ->label('Variabel Kustom')
                    ->keyLabel('Nama Variabel')
                    ->valueLabel('Deskripsi')
                    ->helperText('Tambahkan variabel kustom jika diperlukan (tanpa kurung kurawal)')
                    ->columnSpan(2),

                Textarea::make('description')
                    ->label('Deskripsi Template')
                    ->rows(3)
                    ->helperText('Deskripsi penggunaan template ini')
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => 
                        WhatsappMessageTemplate::getAvailableTypes()[$state] ?? $state
                    )
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        WhatsappMessageTemplate::TYPE_REGISTRATION_VERIFICATION => 'primary',
                        WhatsappMessageTemplate::TYPE_ORDER_COMPLETION => 'success',
                        WhatsappMessageTemplate::TYPE_PAYMENT_RECEIVED => 'warning',
                        WhatsappMessageTemplate::TYPE_COURSE_PURCHASE => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(30)
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                // TextColumn::make('message')
                //     ->label('Isi Pesan')
                //     ->limit(50)
                //     ->wrap(),

                // TextColumn::make('created_at')
                //     ->label('Dibuat')
                //     ->dateTime()
                //     ->sortable(),

                // TextColumn::make('updated_at')
                //     ->label('Diperbarui')
                //     ->dateTime()
                //     ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Template')
                    ->options(WhatsappMessageTemplate::getAvailableTypes()),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->modalHeading('Detail Template WhatsApp')
                    ->modalContent(fn (WhatsappMessageTemplate $record) => view('filament.resources.whatsapp-message-template.show', compact('record')))
                    ->modalWidth('4xl')
                    ->form([]), // Menghapus form fields default
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
            'index' => Pages\ListWhatsappMessageTemplates::route('/'),
            'create' => Pages\CreateWhatsappMessageTemplate::route('/create'),
            'edit' => Pages\EditWhatsappMessageTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(WhatsappMessageTemplateRepositoryInterface::class)->filamentTableQuery();
    }
}
