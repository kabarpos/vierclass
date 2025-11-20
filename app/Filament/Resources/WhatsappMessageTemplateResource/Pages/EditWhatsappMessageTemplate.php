<?php

namespace App\Filament\Resources\WhatsappMessageTemplateResource\Pages;

use App\Filament\Resources\WhatsappMessageTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappMessageTemplate extends EditRecord
{
    protected static string $resource = WhatsappMessageTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Template pesan WhatsApp berhasil diperbarui';
    }
}