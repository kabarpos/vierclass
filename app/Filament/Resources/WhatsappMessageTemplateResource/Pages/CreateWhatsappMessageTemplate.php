<?php

namespace App\Filament\Resources\WhatsappMessageTemplateResource\Pages;

use App\Filament\Resources\WhatsappMessageTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappMessageTemplate extends CreateRecord
{
    protected static string $resource = WhatsappMessageTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Template pesan WhatsApp berhasil dibuat';
    }
}