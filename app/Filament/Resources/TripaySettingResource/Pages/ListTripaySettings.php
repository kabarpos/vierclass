<?php

namespace App\Filament\Resources\TripaySettingResource\Pages;

use App\Filament\Resources\TripaySettingResource;
use App\Models\TripaySetting;
use App\Repositories\TripaySettingRepositoryInterface;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Notifications\Notification;

class ListTripaySettings extends ListRecords
{
    protected static string $resource = TripaySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Konfigurasi')
                ->extraAttributes(['class' => 'cursor-pointer']),

            Actions\Action::make('test_connection')
                ->label('Tes Konfigurasi Aktif')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->action(function () {
                    /** @var TripaySettingRepositoryInterface $repo */
                    $repo = app(TripaySettingRepositoryInterface::class);
                    $config = $repo->getConfig();
                    $active = TripaySetting::query()->where('is_active', true)->first();

                    Notification::make()
                        ->title('Konfigurasi Tripay')
                        ->body('Environment: ' . (($config['isProduction'] ?? false) ? 'Production' : 'Sandbox') . "\n" .
                            'Merchant Code: ' . ($config['merchantCode'] ?? '-') . "\n" .
                            'Sumber: ' . ($active ? 'Database (aktif)' : 'ENV/Config'))
                        ->success()
                        ->send();
                }),
        ];
    }
}

