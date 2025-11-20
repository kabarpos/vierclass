<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;

use App\Models\CourseSection;
use App\Models\SectionContent;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionContentResource extends Resource
{
    protected static ?string $model = SectionContent::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static string | \UnitEnum | null $navigationGroup = 'Products';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $navigationLabel = 'Konten Bagian';
    
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'section-contents';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Course')
                    ->dehydrated(false)
                    ->options(function () {
                        $user = auth()->user();
                        $query = \App\Models\Course::query();

                        // Jika mentor, batasi ke course yang di-mentori
                        if ($user && $user->hasRole('mentor') && !$user->hasAnyRole(['admin', 'super-admin'])) {
                            $query->whereHas('courseMentors', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                        }

                        // Eksklusi course yang terhapus (soft delete)
                        $query->whereNull('deleted_at');

                        return $query
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->extraAttributes(['class' => 'cursor-pointer']),

                Select::make('course_section_id')
                    ->label('Course Section')
                    ->options(function (Get $get) {
                        $courseId = $get('course_id');
                        if (!$courseId) {
                            return [];
                        }

                        $user = auth()->user();
                        $query = CourseSection::with('course')
                            ->where('course_id', $courseId)
                            ->whereHas('course', function ($q) {
                                $q->whereNull('deleted_at');
                            });

                        // Jika mentor, batasi ke section dari course yang di-mentori
                        if ($user && $user->hasRole('mentor') && !$user->hasAnyRole(['admin', 'super-admin'])) {
                            $query->whereHas('course.courseMentors', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                        }

                        return $query
                            ->orderBy('position')
                            ->get()
                            ->mapWithKeys(function ($section) {
                                return [
                                    $section->id => $section->course
                                        ? "{$section->course->name} - {$section->name}"
                                        : $section->name,
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get) => empty($get('course_id')))
                    ->extraAttributes(['class' => 'cursor-pointer']),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('youtube_url')
                    ->label('YouTube URL')
                    ->url()
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText('Optional: Add a YouTube video URL to display a player above the content')
                    ->maxLength(500),

                Select::make('is_free')
                    ->label('Free Preview')
                    ->options([
                        true => 'Yes - Allow free preview',
                        false => 'No - Premium only',
                    ])
                    ->default(false)
                    ->required(),

                RichEditor::make('content')
                    ->label('Content')
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'min-height: 20rem;'])
                    ->toolbarButtons([
                        'undo',
                        'redo',
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'codeBlock',
                        'code',
                        'link',
                        'attachFiles',
                        'table',
                        'horizontalRule',
                        'clearFormatting',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                IconColumn::make('is_free')
                    ->label('Free Preview')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('youtube_url')
                    ->label('Has Video')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->youtube_url))
                    ->sortable(),

                TextColumn::make('courseSection.name')
                    ->label('Section')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),

                TextColumn::make('courseSection.course.name')
                    ->label('Course')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                DeleteAction::make()
                    ->requiresConfirmation(false)
                    ->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                    ForceDeleteBulkAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                    RestoreBulkAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SectionContentResource\Pages\ListSectionContents::route('/'),
            'create' => \App\Filament\Resources\SectionContentResource\Pages\CreateSectionContent::route('/create'),
            'edit' => \App\Filament\Resources\SectionContentResource\Pages\EditSectionContent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Standarisasi ke repository untuk query tabel Filament
        $repo = app(\App\Repositories\SectionContentRepositoryInterface::class);
        return $repo->filamentTableQuery();
    }
}
