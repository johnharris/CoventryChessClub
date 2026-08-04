@extends('layouts.members')

@section('title', 'Members and whitelist')

@section('members')
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">Members and whitelist</h1>
        <p class="mt-1.5 max-w-3xl text-stone-600">
            Nobody can create an account unless their email address appears on this whitelist. Add an
            address, send the person their invitation link, and they choose their own password.
        </p>
    </header>

    {{-- ---------- Add to whitelist ---------- --}}
    <form method="POST" action="{{ route('members.whitelist.store') }}" class="card p-5 sm:p-6">
        @csrf

        <h2 class="text-sm font-semibold text-stone-900">Add someone to the whitelist</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="email" class="field-label">Email address <span class="text-red-600">*</span></label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" class="field-input">
            </div>
            <div>
                <label for="wl_name" class="field-label">Name <span class="text-stone-400">(optional)</span></label>
                <input id="wl_name" name="name" type="text" value="{{ old('name') }}" class="field-input">
            </div>
            <div>
                <label for="role" class="field-label">Access level</label>
                <select id="role" name="role" class="field-input">
                    <option value="member" @selected(old('role', 'member') === 'member')>Club member</option>
                    <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <label for="notes" class="field-label">Notes <span class="text-stone-400">(optional)</span></label>
                <input id="notes" name="notes" type="text" maxlength="500"
                       placeholder="e.g. A team captain, joined September 2026"
                       value="{{ old('notes') }}" class="field-input">
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-stone-200 pt-5">
            <button type="submit" class="btn-primary">Add to whitelist</button>
            <p class="text-sm text-stone-500">
                Administrators can manage every post, page, enquiry and account.
            </p>
        </div>
    </form>

    {{-- ---------- Whitelist entries ---------- --}}
    <section class="mt-8">
        <h2 class="text-lg font-semibold text-stone-900">Whitelist</h2>
        <p class="mt-1 text-sm text-stone-600">
            Unused invitations are listed first. Copy an invitation link and send it to the member.
        </p>

        @if ($entries->isEmpty())
            <p class="card mt-4 p-6 text-sm text-stone-600">Nobody on the whitelist yet.</p>
        @else
            <div class="card mt-4 divide-y divide-stone-100">
                @foreach ($entries as $entry)
                    <div class="p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-stone-900">
                                    {{ $entry->name ?: $entry->email }}
                                    <span class="badge ml-1.5 {{ $entry->role === 'admin' ? 'bg-club-100 text-club-800' : 'bg-stone-200 text-stone-700' }}">
                                        {{ $entry->role === 'admin' ? 'Admin' : 'Member' }}
                                    </span>
                                </p>
                                @if ($entry->name)
                                    <p class="text-sm text-stone-500">{{ $entry->email }}</p>
                                @endif
                                <p class="mt-1 text-sm text-stone-500">
                                    @if ($entry->isClaimed())
                                        Account created {{ $entry->claimed_at->format('j M Y') }}
                                        @if ($entry->claimedBy)
                                            by {{ $entry->claimedBy->publicName() }}
                                        @endif
                                    @else
                                        Invitation not used yet
                                    @endif
                                    @if ($entry->invitedBy)
                                        · added by {{ $entry->invitedBy->publicName() }}
                                    @endif
                                </p>
                                @if ($entry->notes)
                                    <p class="mt-1 text-sm text-stone-600 italic">{{ $entry->notes }}</p>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                @unless ($entry->isClaimed())
                                    <form method="POST" action="{{ route('members.whitelist.invite', $entry) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary !py-1.5 text-xs">New link</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('members.whitelist.destroy', $entry) }}"
                                      data-confirm="Remove {{ $entry->email }} from the whitelist?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost !py-1.5 text-xs !text-red-600 hover:!bg-red-50">Remove</button>
                                </form>
                            </div>
                        </div>

                        @if (! $entry->isClaimed() && $entry->invite_token)
                            <div class="mt-3 flex gap-2">
                                <input
                                    id="invite-{{ $entry->id }}"
                                    type="text"
                                    readonly
                                    value="{{ $entry->inviteUrl() }}"
                                    class="w-full rounded-lg bg-stone-50 px-2.5 py-1.5 font-mono text-xs text-stone-600 ring-1 ring-stone-300 ring-inset"
                                >
                                <button type="button" data-copy="#invite-{{ $entry->id }}" class="btn-secondary shrink-0 !px-3 !py-1.5 text-xs">
                                    Copy
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-5">{{ $entries->links() }}</div>
        @endif
    </section>

    {{-- ---------- Accounts ---------- --}}
    <section class="mt-10">
        <h2 class="text-lg font-semibold text-stone-900">Accounts</h2>
        <p class="mt-1 text-sm text-stone-600">
            Change someone's access level, or suspend an account to revoke access immediately.
        </p>

        <div class="card mt-4 divide-y divide-stone-100">
            @foreach ($users as $account)
                <form method="POST" action="{{ route('members.users.update', $account) }}" class="p-4">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-wrap items-center gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-club-100 text-sm font-bold text-club-800">
                            {{ $account->initials() }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-stone-900">
                                {{ $account->publicName() }}
                                @if ($account->id === auth()->id())
                                    <span class="text-sm font-normal text-stone-500">(you)</span>
                                @endif
                            </p>
                            <p class="text-sm text-stone-500">
                                {{ $account->email }} · {{ $account->posts()->count() }} {{ Str::plural('post', $account->posts()->count()) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <label class="sr-only" for="role-{{ $account->id }}">Access level</label>
                            <select id="role-{{ $account->id }}" name="role" class="field-input !w-auto !py-1.5 text-sm">
                                <option value="member" @selected($account->role === 'member')>Club member</option>
                                <option value="admin" @selected($account->role === 'admin')>Administrator</option>
                            </select>

                            <label class="flex items-center gap-2 text-sm text-stone-700">
                                <input type="checkbox" name="is_active" value="1" @checked($account->is_active)
                                       class="h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
                                Active
                            </label>

                            <button type="submit" class="btn-secondary !py-1.5 text-xs">Save</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>

        <div class="mt-5">{{ $users->links() }}</div>
    </section>
@endsection
