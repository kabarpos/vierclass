<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\EmailMessageTemplateResource\Pages;
use App\Models\EmailMessageTemplate;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\EmailMessageTemplateRepositoryInterface;

class EmailMessageTemplateResource extends Resource
{
    protected static ?string $model = EmailMessageTemplate::class;

    protected static ?string $navigationLabel = 'Template Email';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'email-message-templates';
    }

    protected static ?string $modelLabel = 'Template Pesan Email';

    protected static ?string $pluralModelLabel = 'Template Pesan Email';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 5;

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
                    ->options(EmailMessageTemplate::getAvailableTypes())
                    ->helperText('Pilih tipe template untuk menentukan variabel yang tersedia')
                    ->columnSpan(2),

                TextInput::make('subject')
                    ->label('Subject')
                    ->maxLength(255)
                    ->helperText('Subject email')
                    ->columnSpan(2),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->helperText('Hanya template aktif yang akan digunakan')
                    ->columnSpan(2),

                Textarea::make('message')
                    ->label('Isi Pesan (HTML/Text)')
                    ->required()
                    ->rows(10)
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
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => EmailMessageTemplate::getAvailableTypes()[$state] ?? $state)
                    ->badge(),
                IconColumn::make('is_active')->label('Status')->boolean(),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(30)
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
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
            'index' => Pages\ListEmailMessageTemplates::route('/'),
            'create' => Pages\CreateEmailMessageTemplate::route('/create'),
            'edit' => Pages\EditEmailMessageTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return app(EmailMessageTemplateRepositoryInterface::class)->filamentTableQuery();
    }
}
