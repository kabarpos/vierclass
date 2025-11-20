<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMyCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected static ?string $title = 'Kursus Saya';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->extraAttributes(['class' => 'cursor-pointer']),
            Action::make('all_courses')
                ->label('Semua Kursus')
                ->icon('heroicon-o-rectangle-stack')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(fn () => CourseResource::getUrl('index')),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $user = auth()->user();
        if ($user && $user->hasRole('mentor') && !$user->hasAnyRole(['admin', 'super-admin'])) {
            // Filter kursus ke kursus yang di-mentori oleh user saat ini.
            $query->whereHas('courseMentors', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query;
    }

    protected function getTableRecordActions(): array
    {
        // Di halaman My Course, tampilkan tombol Edit & View.
        return [
            ViewAction::make()->extraAttributes(['class' => 'cursor-pointer']),
            EditAction::make()->extraAttributes(['class' => 'cursor-pointer']),
        ];
    }
}

