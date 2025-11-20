<?php

namespace App\Filament\Resources\SectionContentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SectionContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSectionContents extends ListRecords
{
    protected static string $resource = SectionContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->extraAttributes(['class' => 'cursor-pointer']),
        ];
    }
}
