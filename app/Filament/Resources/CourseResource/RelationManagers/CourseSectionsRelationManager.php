<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'courseSections';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->prefix('Position'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal break-words']),
                TextColumn::make('position'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->recordActions([
                EditAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                DeleteAction::make()->extraAttributes(['class' => 'cursor-pointer']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->extraAttributes(['class' => 'cursor-pointer']),
                ]),
            ]);
    }
}
