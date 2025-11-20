<?php

namespace App\Filament\Pages\Dashboard;

use Filament\Pages\Page;

class HomeRedirect extends Page
{
    /**
     * Halaman root panel admin: segera mengarahkan ke /admin/data
     */
    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        // Slug kosong agar menangani route dasar panel (/admin)
        return '';
    }

    public function mount()
    {
        return redirect('/admin/data');
    }
}