<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Services\ImageService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->id === auth()->id()),
            \Filament\Actions\ForceDeleteAction::make(),
            \Filament\Actions\RestoreAction::make(),
        ];
    }
    
    protected function fillForm(): void
    {
        $data = $this->record->attributesToArray();
        
        // Convert datetime fields to boolean for toggles
        $data['email_verified_at'] = $this->record->email_verified_at !== null;
        $data['whatsapp_verified_at'] = $this->record->whatsapp_verified_at !== null;
        
        // Handle roles relationship - get the first role ID
        $data['roles'] = $this->record->roles->first()?->id;
        
        $this->form->fill($data);
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Konversi foto ke WebP jika ada dan bertipe JPEG/PNG
        if (!empty($data['photo'])) {
            $data['photo'] = ImageService::convertToWebp($data['photo'], 'public', 85, true);
        }

        // Handle password - only update if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }
        
        // Handle verification toggles - convert boolean to datetime
        if (isset($data['email_verified_at'])) {
            if ($data['email_verified_at']) {
                $data['email_verified_at'] = $record->email_verified_at ?: now();
            } else {
                $data['email_verified_at'] = null;
            }
        }
        
        if (isset($data['whatsapp_verified_at'])) {
            if ($data['whatsapp_verified_at']) {
                $data['whatsapp_verified_at'] = $record->whatsapp_verified_at ?: now();
            } else {
                $data['whatsapp_verified_at'] = null;
            }
        }
        
        // Auto-set is_account_active based on verification status
        if ($data['email_verified_at'] || $data['whatsapp_verified_at']) {
            // If user has at least one verification, allow is_account_active to be set
            $data['is_account_active'] = $data['is_account_active'] ?? false;
        } else {
            // If user has no verification, force is_account_active to false
            $data['is_account_active'] = false;
        }
        
        $record->update($data);
        
        return $record;
    }
}
