<?php

namespace App\Filament\Resources\CourseResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->extraAttributes(['class' => 'cursor-pointer']),
            Action::make('my_courses')
                ->label('Kursus Saya')
                ->icon('heroicon-o-user')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(fn () => CourseResource::getUrl('my-courses')),
        ];
    }
}
