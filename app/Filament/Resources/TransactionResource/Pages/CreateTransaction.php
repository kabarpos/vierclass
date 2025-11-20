<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Services\ImageService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Konversi bukti pembayaran ke WebP jika ada dan bertipe JPEG/PNG (skip jika URL eksternal)
        if (!empty($data['proof']) && !str_starts_with($data['proof'], 'http')) {
            $data['proof'] = ImageService::convertToWebp($data['proof'], 'public', 85, true);
        }
        return $data;
    }
}
