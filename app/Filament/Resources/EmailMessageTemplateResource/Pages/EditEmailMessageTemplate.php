<?php

namespace App\Filament\Resources\EmailMessageTemplateResource\Pages;

use App\Filament\Resources\EmailMessageTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailMessageTemplate extends EditRecord
{
    protected static string $resource = EmailMessageTemplateResource::class;

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
        return 'Template pesan Email berhasil diperbarui';
    }
}