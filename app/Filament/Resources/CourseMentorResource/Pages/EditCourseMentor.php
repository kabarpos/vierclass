<?php

namespace App\Filament\Resources\CourseMentorResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\CourseMentorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseMentor extends EditRecord
{
    protected static string $resource = CourseMentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
