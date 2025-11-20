<?php

namespace App\Filament\Resources\TripaySettingResource\Pages;

use App\Filament\Resources\TripaySettingResource;
use App\Models\TripaySetting;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTripaySetting extends CreateRecord
{
    protected static string $resource = TripaySettingResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        if (!empty($data['is_active'])) {
            TripaySetting::query()->where('is_active', true)->update(['is_active' => false]);
        }
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Konfigurasi Tripay berhasil dibuat')
            ->success()
            ->send();
    }
}

