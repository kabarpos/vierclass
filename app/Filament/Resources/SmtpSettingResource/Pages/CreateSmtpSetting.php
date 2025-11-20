<?php

namespace App\Filament\Resources\SmtpSettingResource\Pages;

use App\Filament\Resources\SmtpSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmtpSetting extends CreateRecord
{
    protected static string $resource = SmtpSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengaturan SMTP berhasil dibuat';
    }
}