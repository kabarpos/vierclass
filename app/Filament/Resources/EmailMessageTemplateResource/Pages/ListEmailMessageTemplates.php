<?php

namespace App\Filament\Resources\EmailMessageTemplateResource\Pages;

use App\Filament\Resources\EmailMessageTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailMessageTemplates extends ListRecords
{
    protected static string $resource = EmailMessageTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Template Email')
                ->icon('heroicon-o-plus'),
        ];
    }
}