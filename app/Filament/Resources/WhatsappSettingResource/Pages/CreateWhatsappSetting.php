<?php

namespace App\Filament\Resources\WhatsappSettingResource\Pages;

use App\Filament\Resources\WhatsappSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappSetting extends CreateRecord
{
    protected static string $resource = WhatsappSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengaturan WhatsApp berhasil dibuat';
    }
}