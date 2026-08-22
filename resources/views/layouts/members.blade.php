@extends('layouts.app')

@section('content')
    @php $user = auth()->user(); @endphp

    <div class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-center gap-4 overflow-x-auto py-3" role="navigation" aria-label="Members area">
                <x-members-tab :href="route('members.dashboard')" :active="request()->routeIs('members.dashboard')">
                    Dashboard
                </x-members-tab>
                <x-members-tab :href="route('members.posts.index')" :active="request()->routeIs('members.posts.*')">
                    {{ $user->isAdmin() ? 'All posts' : 'My posts' }}
                </x-members-tab>
                <x-members-tab :href="route('members.media.index')" :active="request()->routeIs('members.media.*')">
                    Images
                </x-members-tab>

                @if ($user->isAdmin())
                    <x-members-tab :href="route('members.homepage.edit')" :active="request()->routeIs('members.homepage.*')">
                        Homepage
                    </x-members-tab>
                    <x-members-tab :href="route('members.enquiries.index')" :active="request()->routeIs('members.enquiries.*')">
                        Enquiries
                        @if ($unread = \App\Models\Enquiry::unread()->count())
                            <span class="ml-1.5 rounded-full bg-red-100 px-1.5 py-0.5 text-xs font-bold text-red-700">{{ $unread }}</span>
                        @endif
                    </x-members-tab>
                    <x-members-tab :href="route('members.emails.edit')" :active="request()->routeIs('members.emails.*')">
                        Emails
                    </x-members-tab>
                    <x-members-tab :href="route('members.whitelist.index')" :active="request()->routeIs('members.whitelist.*') || request()->routeIs('members.users.*')">
                        Members
                    </x-members-tab>
                    <x-members-tab :href="route('members.pages.index')" :active="request()->routeIs('members.pages.*')">
                        Pages
                    </x-members-tab>
                @endif

                <x-members-tab :href="route('members.profile')" :active="request()->routeIs('members.profile')">
                    My profile
                </x-members-tab>

                <a href="{{ route('members.posts.create') }}" class="btn-primary ml-auto shrink-0 !py-2 text-xs whitespace-nowrap">
                    Write a post
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 p-5 ring-1 ring-red-200">
                <p class="font-semibold text-red-900">Please check the following</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('members')
    </div>
@endsection
