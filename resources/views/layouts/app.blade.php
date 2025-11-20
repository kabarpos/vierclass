<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', \App\Helpers\WebsiteSettingHelper::get('site_name', config('app.name', 'Laravel')))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Ensure elements with x-cloak are hidden before Alpine initializes -->
        <style>[x-cloak]{display:none!important}</style>

        <!-- Styles & Scripts -->
        @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])
    </head>
    <body class="font-manrope antialiased bg-rebel-black-1000">
        <div class="min-h-screen">
            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </body>
</html>
