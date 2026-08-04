@extends('layouts.guest')

@section('title', "Members' login")

@section('content')
    <h1 class="text-2xl font-bold tracking-tight text-stone-900">Members' login</h1>
    <p class="mt-2 text-sm leading-relaxed text-stone-600">
        This area is for Coventry Chess Club members. Accounts are created by the club
        administrators — there is no public sign-up.
    </p>

    @if ($errors->any())
        <div class="mt-5 rounded-lg bg-red-50 p-4 ring-1 ring-red-200">
            <ul class="space-y-1 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">Email address</label>
            <input id="email" name="email" type="email" required autofocus autocomplete="username"
                   value="{{ old('email') }}" class="field-input">
        </div>

        <div>
            <label for="password" class="field-label">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="field-input">
        </div>

        <div class="flex items-center gap-2.5">
            <input id="remember" name="remember" type="checkbox" value="1"
                   class="h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
            <label for="remember" class="text-sm text-stone-700">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <div class="mt-6 border-t border-stone-200 pt-5 text-sm text-stone-600">
        <p>
            Been added to the club whitelist but not set your password yet?
            <a href="{{ route('register') }}" class="font-semibold text-club-700 hover:text-club-900">Create your account</a>.
        </p>
        <p class="mt-2.5">
            Forgotten your password? Please
            <a href="{{ route('contact') }}" class="font-semibold text-club-700 hover:text-club-900">contact a club administrator</a>,
            who can reset it for you.
        </p>
    </div>
@endsection
