<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') — {{ config('club.name') }}@else{{ config('club.name') }} — {{ config('club.tagline') }}@endif</title>

    <meta name="description" content="@yield('description', config('club.tagline'))">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name" content="{{ config('club.name') }}">
    <meta property="og:title" content="@hasSection('title')@yield('title')@else{{ config('club.name') }}@endif">
    <meta property="og:description" content="@yield('description', config('club.tagline'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col">
    <a href="#main" class="skip-link">Skip to content</a>

    @include('partials.header')

    @if (session('status'))
        <div class="no-print bg-club-700 text-white">
            <div class="mx-auto max-w-5xl px-4 py-3 text-sm font-medium sm:px-6">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
