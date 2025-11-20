<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\CourseMentorResource\Pages\ListCourseMentors;
use App\Filament\Resources\CourseMentorResource\Pages\CreateCourseMentor;
use App\Filament\Resources\CourseMentorResource\Pages\EditCourseMentor;
use App\Filament\Resources\CourseMentorResource\Pages;
use App\Filament\Resources\CourseMentorResource\RelationManagers;
use App\Models\CourseMentor;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseMentorResource extends Resource
{
    protected static ?string $model = CourseMentor::class;

    protected static string | \BackedEnum | null $navigationIcon = null;

    protected static string | \UnitEnum | null $navigationGroup = 'Products';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationLabel = 'Mentor Kursus';
    
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'course-mentors';
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                Select::make('course_id')
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('user_id')
                    ->label('Mentor')
                    ->options(function () {
                        return User::role('mentor')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('about')
                    ->required(),

                Select::make('is_active')
                    ->options([
                        true => 'Active',
                        false => 'Banned',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('mentor.photo')
                    ->defaultImageUrl(fn ($record) => getUserAvatarWithColor($record->mentor, 100))
                    ->extraAttributes(['class' => 'cursor-pointer']),

                TextColumn::make('mentor.name')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'cursor-pointer whitespace-normal break-words']),

                ImageColumn::make('course.thumbnail')
                    ->extraAttributes(['class' => 'cursor-pointer']),

                TextColumn::make('course.name')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'cursor-pointer whitespace-normal break-words']),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
            'index' => ListCourseMentors::route('/'),
            'create' => CreateCourseMentor::route('/create'),
            'edit' => EditCourseMentor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Standarisasi ke repository untuk query tabel Filament
        $repo = app(\App\Repositories\CourseMentorRepositoryInterface::class);
        return $repo->filamentTableQuery();
    }
}
