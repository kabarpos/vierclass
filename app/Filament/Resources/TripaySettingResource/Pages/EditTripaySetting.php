<?php

namespace App\Filament\Resources\TripaySettingResource\Pages;

use App\Filament\Resources\TripaySettingResource;
use App\Models\TripaySetting;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Notifications\Notification;

class EditTripaySetting extends EditRecord
{
    protected static string $resource = TripaySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->extraAttributes(['class' => 'cursor-pointer']),

            Actions\Action::make('activate')
                ->label('Aktifkan Konfigurasi Ini')
                ->requiresConfirmation()
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->visible(fn () => !$this->record->is_active)
                ->action(function () {
                    TripaySetting::query()->where('is_active', true)->update(['is_active' => false]);
                    $this->record->is_active = true;
                    $this->record->save();

                    Notification::make()
                        ->title('Konfigurasi Tripay diaktifkan')
                        ->success()
                        ->send();
                }),
        ];
    }
}

