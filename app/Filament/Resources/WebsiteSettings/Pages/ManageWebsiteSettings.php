<?php

namespace App\Filament\Resources\WebsiteSettings\Pages;

use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use App\Models\WebsiteSetting;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageWebsiteSettings extends ManageRecords
{
    protected static string $resource = WebsiteSettingResource::class;

    protected static ?string $title = 'Pengaturan Website';

    public function mount(): void
    {
        // Pastikan ada record pengaturan website
        $settings = WebsiteSetting::getInstance();
        
        // Redirect ke edit form jika ada record
        if ($settings->exists) {
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $settings]));
        }
    }

    protected function getHeaderActions(): array
    {
        $settings = WebsiteSetting::getInstance();
        
        if (!$settings->exists) {
            return [
                Action::make('create_settings')
                    ->label('Buat Pengaturan Website')
                    ->icon('heroicon-o-plus')
                    ->action(function () {
                        $settings = WebsiteSetting::getInstance();
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $settings]));
                    })
            ];
        }

        return [
            Action::make('edit_settings')
                ->label('Edit Pengaturan')
                ->icon('heroicon-o-pencil')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $settings]))
        ];
    }

    public function getTitle(): string
    {
        return 'Pengaturan Website';
    }
}
