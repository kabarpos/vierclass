<?php

namespace App\Filament\Resources\WhatsappSettingResource\Pages;

use App\Filament\Resources\WhatsappSettingResource;
use App\Services\DripsenderService;
use App\Models\WhatsappSetting;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWhatsappSetting extends EditRecord
{
    protected static string $resource = WhatsappSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label('Test Koneksi API')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->action(function () {
                    try {
                        // Get current form state
                        $formData = $this->form->getState();
                        
                        // Create a temporary WhatsappSetting instance with form data
                        $tempSetting = new WhatsappSetting();
                        $tempSetting->fill($formData);
                        
                        // Create DripsenderService with the temporary setting
                        $originalSetting = WhatsappSetting::getActive();
                        
                        // Temporarily set this as the active setting for testing
                        if ($originalSetting) {
                            $originalSetting->update(['is_active' => false]);
                        }
                        
                        // Save the temp setting for testing
                        $tempSetting->save();
                        
                        // Test connection
                        $dripsenderService = new DripsenderService();
                        $result = $dripsenderService->testConnection();
                        
                        // Restore original active setting
                        $tempSetting->delete();
                        if ($originalSetting) {
                            $originalSetting->update(['is_active' => true]);
                        }

                        if ($result['success']) {
                            Notification::make()
                                ->title('✅ Koneksi Berhasil!')
                                ->body($result['message'] ?? 'API key valid dan terhubung dengan Dripsender')
                                ->success()
                                ->duration(5000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('❌ Koneksi Gagal')
                                ->body($result['message'] ?? 'Tidak dapat terhubung ke Dripsender. Periksa API key Anda.')
                                ->danger()
                                ->duration(8000)
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('⚠️ Error Koneksi')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->duration(8000)
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengaturan WhatsApp berhasil diperbarui';
    }
}