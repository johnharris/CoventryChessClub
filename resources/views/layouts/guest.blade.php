<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('club.name') }}</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-club-900">
    <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6">
        <div class="mx-auto w-full max-w-md">
            <a href="{{ route('home') }}" class="flex items-center justify-center gap-2.5 text-white">
                <x-knight-mark class="h-9 w-9 text-club-300" />
                <span class="flex flex-col leading-none">
                    <span class="text-lg font-bold tracking-tight">Coventry</span>
                    <span class="text-[0.7rem] font-medium tracking-[0.18em] text-club-300 uppercase">Chess Club</span>
                </span>
            </a>

            <div class="mt-8 rounded-2xl bg-white p-7 shadow-xl sm:p-9">
                @yield('content')
            </div>

            <p class="mt-6 text-center text-sm text-club-300">
                <a href="{{ route('home') }}" class="hover:text-white">&larr; Back to the club website</a>
            </p>
        </div>
    </div>
</body>
</html>
