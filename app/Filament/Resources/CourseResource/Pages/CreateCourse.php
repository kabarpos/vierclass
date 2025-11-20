<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Services\ImageService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['thumbnail'])) {
            $data['thumbnail'] = ImageService::convertToWebp($data['thumbnail'], 'public', 85, true);
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $user = auth()->user();
        if ($user && $user->hasRole('mentor') && !$user->hasAnyRole(['admin', 'super-admin'])) {
            // Pastikan mentor pembuat course otomatis terdaftar sebagai courseMentor
            $this->record->courseMentors()->firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'is_active' => true,
                    'about' => 'Mentor pemilik kursus',
                ]
            );
        }
    }
}
