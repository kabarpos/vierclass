<?php

namespace App\Filament\Resources\WebsiteSettings\Pages;

use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use App\Services\ImageService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWebsiteSetting extends EditRecord
{
    protected static string $resource = WebsiteSettingResource::class;

    protected static ?string $title = 'Edit Pengaturan Website';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon('heroicon-o-check')
                ->action('save')
                ->color('success'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengaturan website berhasil disimpan')
            ->body('Semua perubahan pengaturan website telah disimpan.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Konversi logo & default_thumbnail ke WebP jika JPEG/PNG
        if (!empty($data['logo'])) {
            $data['logo'] = ImageService::convertToWebp($data['logo'], 'public', 85, true);
        }

        // Jangan konversi favicon (bisa ICO/SVG/PNG kecil)

        if (!empty($data['default_thumbnail'])) {
            $data['default_thumbnail'] = ImageService::convertToWebp($data['default_thumbnail'], 'public', 85, true);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Edit Pengaturan Website';
    }
}
