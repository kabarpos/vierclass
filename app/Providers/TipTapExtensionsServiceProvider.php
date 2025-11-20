<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TipTapExtensionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Custom TipTap extensions will be loaded via JavaScript
        // Extensions are configured per RichEditor component
    }
}