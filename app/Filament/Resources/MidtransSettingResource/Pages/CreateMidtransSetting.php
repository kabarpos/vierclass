<?php

namespace App\Filament\Resources\MidtransSettingResource\Pages;

use App\Filament\Resources\MidtransSettingResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateMidtransSetting extends CreateRecord
{
    protected static string $resource = MidtransSettingResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function beforeCreate(): void
    {
        // If this is set to active, deactivate others
        if ($this->data['is_active'] ?? false) {
            \App\Models\MidtransSetting::query()->update(['is_active' => false]);
        }
    }
    
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Configuration Created')
            ->body('Midtrans configuration has been created successfully.')
            ->success()
            ->send();
    }
}