<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])
        @stack('after-styles')
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
        {{-- Global font diset via resources/css/app.css & custom.css --}}
        
        @php
            use App\Helpers\WebsiteSettingHelper;
            $metaTags = WebsiteSettingHelper::getMetaTags(isset($pageTitle) ? $pageTitle : null, isset($pageDescription) ? $pageDescription : null);
        @endphp
        
        <title>{{ $metaTags['title'] ?? (trim($__env->yieldContent('title')) ?: WebsiteSettingHelper::getSiteName()) }}</title>
        <meta name="description" content="{{ $metaTags['description'] ?? WebsiteSettingHelper::getSiteDescription() }}">
        @if($metaTags['keywords'])
            <meta name="keywords" content="{{ $metaTags['keywords'] }}">
        @endif
        @if($metaTags['author'])
            <meta name="author" content="{{ $metaTags['author'] }}">
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicon -->
        @if(WebsiteSettingHelper::getFaviconUrl())
            <link rel="icon" type="image/png" sizes="32x32" href="{{ WebsiteSettingHelper::getFaviconUrl() }}">
            <link rel="apple-touch-icon" href="{{ WebsiteSettingHelper::getFaviconUrl() }}">
        @else
            <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/logos/favicon.svg') }}">
            <link rel="apple-touch-icon" href="{{ asset('assets/images/logos/favicon.svg') }}">
        @endif

        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="{{ $metaTags['title'] ?? 'LMS Online Learning Platform - Learn Anytime, Anywhere' }}">
        <meta property="og:description" content="{{ $metaTags['description'] ?? 'LMS is an innovative online learning platform that empowers students and professionals with high-quality, accessible courses.' }}">
        <meta property="og:image" content="{{ WebsiteSettingHelper::getDefaultThumbnailUrl() ?? '/assets/images/logos/logo-64-big.png' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        @if($metaTags['site_name'])
            <meta property="og:site_name" content="{{ $metaTags['site_name'] }}">
        @endif

        <!-- Custom CSS -->
        @if(WebsiteSettingHelper::getCustomCss())
            <style>
                {!! WebsiteSettingHelper::getCustomCss() !!}
            </style>
        @endif

        <!-- Head Scripts -->
        @if(WebsiteSettingHelper::getHeadScripts())
            {!! WebsiteSettingHelper::getHeadScripts() !!}
        @endif

        <!-- Google Analytics -->
        {!! WebsiteSettingHelper::getGoogleAnalyticsScript() !!}

        <!-- Facebook Pixel -->
        {!! WebsiteSettingHelper::getFacebookPixelScript() !!}
    </head>
    <body class="font-manrope">
        <div class="min-h-screen flex flex-col">
            <div class="flex-1">
                @yield('content')
            </div>
            <x-simple-footer />
        </div>

        <!-- Body Scripts -->
        @if(WebsiteSettingHelper::getBodyScripts())
            {!! WebsiteSettingHelper::getBodyScripts() !!}
        @endif

        @stack('scripts')
        @stack('after-scripts')
    </body>
</html>
