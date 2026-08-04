@extends('layouts.guest')

@section('title', 'Create your account')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight text-stone-900">Create your account</h1>

    @if ($entry)
        <div class="mt-4 rounded-lg bg-club-50 p-4 ring-1 ring-club-200">
            <p class="text-sm text-club-900">
                Welcome{{ $entry->name ? ', '.$entry->name : '' }}. You have been invited to the
                club site as
                <strong class="font-semibold">{{ $entry->role === 'admin' ? 'an administrator' : 'a club member' }}</strong>.
                Choose a password below to finish setting up your account.
            </p>
        </div>
    @else
        <p class="mt-2 text-sm leading-relaxed text-stone-600">
            Accounts are for club members only. Your email address must already have been added to
            the club whitelist by an administrator — if it has not, please
            <a href="{{ route('contact') }}" class="font-semibold text-club-700 hover:text-club-900">ask us to add you</a> first.
        </p>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-lg bg-red-50 p-4 ring-1 ring-red-200">
            <ul class="space-y-1 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
        @csrf

        @if ($token)
            <input type="hidden" name="token" value="{{ $token }}">
        @endif

        <div>
            <label for="name" class="field-label">Your name</label>
            <input id="name" name="name" type="text" required autofocus autocomplete="name"
                   value="{{ old('name', $entry->name ?? '') }}" class="field-input">
        </div>

        <div>
            <label for="email" class="field-label">Email address</label>
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email', $entry->email ?? '') }}"
                   @readonly((bool) $entry)
                   class="field-input {{ $entry ? 'bg-stone-100' : '' }}">
            @if ($entry)
                <p class="field-hint">This is the address your invitation was sent to.</p>
            @else
                <p class="field-hint">Use the address the club has on file for you.</p>
            @endif
        </div>

        <div>
            <label for="password" class="field-label">Choose a password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="field-input">
            <p class="field-hint">At least 8 characters, including a letter and a number.</p>
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirm your password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   autocomplete="new-password" class="field-input">
        </div>

        <button type="submit" class="btn-primary w-full">Create my account</button>
    </form>

    <p class="mt-6 border-t border-stone-200 pt-5 text-sm text-stone-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-club-700 hover:text-club-900">Sign in</a>.
    </p>
@endsection
