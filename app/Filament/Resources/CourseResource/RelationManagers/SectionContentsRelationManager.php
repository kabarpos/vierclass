<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Models\CourseSection;
use Filament\Schemas\Schema;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SectionContentsRelationManager extends RelationManager
{
    protected static string $relationship = 'sectionContents';

    protected static ?string $title = 'Konten Bagian';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_section_id')
                    ->label('Course Section')
                    ->options(function () {
                        $owner = $this->getOwnerRecord();
                        return CourseSection::query()
                            ->where('course_id', $owner->id)
                            ->orderBy('position')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ])
            ->headerActions([
                CreateAction::make()->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->recordActions([
                EditAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                DeleteAction::make()->requiresConfirmation(false)->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(false)->extraAttributes(['class' => 'cursor-pointer']),
                ]),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $repo = app(\App\Repositories\SectionContentRepositoryInterface::class);
        return $repo->filamentTableQuery()
            ->whereHas('courseSection', function ($q) {
                $q->where('course_id', $this->getOwnerRecord()->id);
            });
    }
}

