<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\ImageService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function handleRecordCreation(array $data): Model
    {
        // Konversi foto ke WebP jika ada dan bertipe JPEG/PNG
        if (!empty($data['photo'])) {
            $data['photo'] = ImageService::convertToWebp($data['photo'], 'public', 85, true);
        }

        // Handle verification toggles - convert boolean to datetime
        if (isset($data['email_verified_at']) && $data['email_verified_at']) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }
        
        if (isset($data['whatsapp_verified_at']) && $data['whatsapp_verified_at']) {
            $data['whatsapp_verified_at'] = now();
        } else {
            $data['whatsapp_verified_at'] = null;
        }
        
        // Auto-set is_account_active based on verification status
        $data['is_account_active'] = ($data['email_verified_at'] || $data['whatsapp_verified_at']) && ($data['is_account_active'] ?? false);
        
        return static::getModel()::create($data);
    }
}
