@extends('layouts.members')

@section('title', 'My profile')

@section('members')
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">My profile</h1>
        <p class="mt-1.5 text-stone-600">
            Your name appears on the posts you write. Your email address is used only for signing in.
        </p>
    </header>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- ---------- Details ---------- --}}
        <form method="POST" action="{{ route('members.profile.update') }}" class="card p-5 sm:p-6">
            @csrf
            @method('PUT')

            <h2 class="text-sm font-semibold text-stone-900">Your details</h2>

            <div class="mt-5 space-y-5">
                <div>
                    <label for="name" class="field-label">Full name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="field-input">
                </div>

                <div>
                    <label for="display_name" class="field-label">
                        Name shown on posts <span class="text-stone-400">(optional)</span>
                    </label>
                    <input id="display_name" name="display_name" type="text"
                           value="{{ old('display_name', $user->display_name) }}" class="field-input">
                    <p class="field-hint">Leave blank to use your full name.</p>
                </div>

                <div>
                    <label for="email" class="field-label">Email address</label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}" class="field-input">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="ecf_code" class="field-label">ECF code <span class="text-stone-400">(optional)</span></label>
                        <input id="ecf_code" name="ecf_code" type="text" value="{{ old('ecf_code', $user->ecf_code) }}" class="field-input">
                    </div>
                    <div>
                        <label for="ecf_rating" class="field-label">ECF rating <span class="text-stone-400">(optional)</span></label>
                        <input id="ecf_rating" name="ecf_rating" type="number" min="0" max="3500"
                               value="{{ old('ecf_rating', $user->ecf_rating) }}" class="field-input">
                    </div>
                </div>

                <div>
                    <label for="bio" class="field-label">A line about you <span class="text-stone-400">(optional)</span></label>
                    <textarea id="bio" name="bio" rows="3" maxlength="1000" class="field-input">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>

            <div class="mt-6 border-t border-stone-200 pt-5">
                <button type="submit" class="btn-primary">Save profile</button>
            </div>
        </form>

        {{-- ---------- Password + account ---------- --}}
        <div class="space-y-6">
            <form method="POST" action="{{ route('members.password.update') }}" class="card p-5 sm:p-6">
                @csrf
                @method('PUT')

                <h2 class="text-sm font-semibold text-stone-900">Change your password</h2>

                <div class="mt-5 space-y-5">
                    <div>
                        <label for="current_password" class="field-label">Current password</label>
                        <input id="current_password" name="current_password" type="password" required
                               autocomplete="current-password" class="field-input">
                    </div>
                    <div>
                        <label for="password" class="field-label">New password</label>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password" class="field-input">
                        <p class="field-hint">At least 8 characters, including a letter and a number.</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="field-label">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" class="field-input">
                    </div>
                </div>

                <div class="mt-6 border-t border-stone-200 pt-5">
                    <button type="submit" class="btn-primary">Change password</button>
                </div>
            </form>

            <div class="card p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-stone-900">Your account</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Access level</dt>
                        <dd class="font-medium text-stone-900">{{ $user->isAdmin() ? 'Administrator' : 'Club member' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Member since</dt>
                        <dd class="font-medium text-stone-900">{{ $user->created_at->format('F Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-stone-500">Posts written</dt>
                        <dd class="font-medium text-stone-900">{{ $user->posts()->count() }}</dd>
                    </div>
                </dl>
                @unless ($user->isAdmin())
                    <p class="mt-4 border-t border-stone-200 pt-4 text-sm text-stone-600">
                        Only club administrators can change access levels. Ask an administrator if you
                        need different permissions.
                    </p>
                @endunless
            </div>
        </div>
    </div>
@endsection
