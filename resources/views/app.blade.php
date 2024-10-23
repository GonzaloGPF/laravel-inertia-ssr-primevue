<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="app_url" content="{{ config('app.url') }}">
        <meta name="app_name" content="{{ config('app.name') }}">
        <meta name="app_version" content="{{ Inertia\Inertia::getVersion() }}">
        <meta name="company_name" content="{{ config('app.name') }}">
        <meta name="company_acronym" content="{{ preg_replace('/\b(\w)|./', '$1', config('app.name')) }}">
        <meta property="og:locale" content="es_ES"/>
        <meta property="og:url" content="{{ config('app.url') }}"/>
        <meta property="og:type" content="website"/>
        <meta property="og:title" content="{{ config('app.name') }}"/>
        <meta property="og:description" content={{ config('app.description') }} />
        <meta property="og:image" content="{{ url('images/logo.png') }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="200">
        <meta property="og:image:height" content="200">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
        <link rel="icon" href="{{ asset('images/favicon.ico') }}" sizes="any"><!-- 32×32 -->
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}"><!-- 180×180 -->

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
