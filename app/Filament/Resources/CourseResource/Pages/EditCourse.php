<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Services\ImageService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['thumbnail'])) {
            $data['thumbnail'] = ImageService::convertToWebp($data['thumbnail'], 'public', 85, true);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Kursus')
                ->modalDescription('Menghapus kursus akan menghapus semua Course Section dan Section Content terkait. Jika Anda melakukan Force Delete, penghapusan akan bersifat permanen.')
                ->extraAttributes(['class' => 'cursor-pointer']),
            ForceDeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Permanen Kursus')
                ->modalDescription('Tindakan ini akan menghapus PERMANEN kursus beserta seluruh Course Section dan Section Content terkait (cascade). Tindakan tidak dapat dibatalkan.')
                ->extraAttributes(['class' => 'cursor-pointer']),
            RestoreAction::make()->extraAttributes(['class' => 'cursor-pointer']),
        ];
    }
}
