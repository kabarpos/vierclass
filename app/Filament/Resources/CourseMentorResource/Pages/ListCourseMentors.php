<?php

namespace App\Filament\Resources\CourseMentorResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CourseMentorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseMentors extends ListRecords
{
    protected static string $resource = CourseMentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
