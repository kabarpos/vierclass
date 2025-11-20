<?php

namespace App\Filament\Resources\WhatsappMessageTemplateResource\Pages;

use App\Filament\Resources\WhatsappMessageTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappMessageTemplates extends ListRecords
{
    protected static string $resource = WhatsappMessageTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Template')
                ->icon('heroicon-o-plus'),
        ];
    }
}