<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Services\ImageService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Konversi bukti pembayaran ke WebP jika ada dan bertipe JPEG/PNG (skip jika URL eksternal)
        if (!empty($data['proof']) && !str_starts_with($data['proof'], 'http')) {
            $data['proof'] = ImageService::convertToWebp($data['proof'], 'public', 85, true);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
