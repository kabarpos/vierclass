<?php

namespace App\Filament\Resources\MidtransSettingResource\Pages;

use App\Filament\Resources\MidtransSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMidtransSetting extends EditRecord
{
    protected static string $resource = MidtransSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('activate')
                ->label('Activate This Configuration')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    // Deactivate all other configurations
                    \App\Models\MidtransSetting::where('id', '!=', $this->record->id)
                        ->update(['is_active' => false]);
                    
                    // Activate this configuration
                    $this->record->update(['is_active' => true]);
                    
                    Notification::make()
                        ->title('Configuration Activated')
                        ->body('This Midtrans configuration is now active.')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->modalDescription('This will deactivate all other configurations and activate this one.')
                ->visible(fn () => !$this->record->is_active),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function beforeSave(): void
    {
        // If this is set to active, deactivate others
        if ($this->data['is_active'] ?? false) {
            \App\Models\MidtransSetting::where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }
    
    protected function afterSave(): void
    {
        Notification::make()
            ->title('Configuration Updated')
            ->body('Midtrans configuration has been updated successfully.')
            ->success()
            ->send();
    }
}