<?php

namespace App\Filament\Resources\WhatsappSettingResource\Pages;

use App\Filament\Resources\WhatsappSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappSettings extends ListRecords
{
    protected static string $resource = WhatsappSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pengaturan WhatsApp')
                ->icon('heroicon-o-plus'),
        ];
    }
}