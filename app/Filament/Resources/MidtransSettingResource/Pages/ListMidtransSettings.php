<?php

namespace App\Filament\Resources\MidtransSettingResource\Pages;

use App\Filament\Resources\MidtransSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListMidtransSettings extends ListRecords
{
    protected static string $resource = MidtransSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add New Configuration')
                ->icon('heroicon-o-plus'),
                
            Action::make('test_connection')
                ->label('Test Connection')
                ->icon('heroicon-o-wifi')
                ->color('info')
                ->action(function () {
                    $activeConfig = \App\Models\MidtransSetting::getActiveConfig();
                    
                    if (!$activeConfig) {
                        Notification::make()
                            ->title('No Active Configuration')
                            ->body('Please create and activate a Midtrans configuration first.')
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    if (!$activeConfig->isValidConfig()) {
                        Notification::make()
                            ->title('Invalid Configuration')
                            ->body('Server Key and Client Key are required.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Here you could add actual API test logic
                    Notification::make()
                        ->title('Configuration Loaded')
                        ->body("Using {$activeConfig->environment_text} environment with Merchant ID: {$activeConfig->merchant_id}")
                        ->success()
                        ->send();
                }),
        ];
    }
}