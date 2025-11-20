<?php

namespace App\Filament\Resources\EmailMessageTemplateResource\Pages;

use App\Filament\Resources\EmailMessageTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailMessageTemplate extends CreateRecord
{
    protected static string $resource = EmailMessageTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Template pesan Email berhasil dibuat';
    }
}