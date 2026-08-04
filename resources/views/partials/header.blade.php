@php
    $navPages = \App\Models\Page::published()->inNav()->get(['title', 'slug']);
    $user = auth()->user();
@endphp

<header class="no-print sticky top-0 z-40 border-b border-club-800/40 bg-club-900 text-club-50">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex h-16 items-center justify-between gap-4">

            {{-- Club name / logo --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <x-knight-mark class="h-8 w-8 text-club-200" />
                <span class="flex flex-col leading-none">
                    <span class="text-base font-bold tracking-tight sm:text-lg">Coventry</span>
                    <span class="text-[0.7rem] font-medium tracking-[0.18em] text-club-300 uppercase">Chess Club</span>
                </span>
            </a>

            {{-- Desktop navigation --}}
            <nav class="hidden items-center gap-1 md:flex" aria-label="Main">
                <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-nav-link>
                <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">Blog</x-nav-link>

                @foreach ($navPages as $page)
                    <x-nav-link
                        :href="route('pages.show', $page)"
                        :active="request()->routeIs('pages.show') && request()->route('page')?->slug === $page->slug"
                    >{{ $page->title }}</x-nav-link>
                @endforeach

                <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>
            </nav>

            {{-- Account --}}
            <div class="flex items-center gap-2">
                @auth
                    <div class="relative hidden md:block" data-dropdown>
                        <button
                            type="button"
                            data-dropdown-toggle
                            aria-expanded="false"
                            class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-sm font-medium text-club-100 transition-colors hover:bg-club-800"
                        >
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-club-700 text-xs font-bold text-white">
                                {{ $user->initials() }}
                            </span>
                            <span class="max-w-28 truncate">{{ $user->publicName() }}</span>
                            <svg class="h-4 w-4 text-club-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.06l3.71-3.83a.75.75 0 1 1 1.08 1.04l-4.25 4.4a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            data-dropdown-menu
                            hidden
                            class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-stone-900/10"
                        >
                            <div class="border-b border-stone-100 px-4 pt-1 pb-2.5">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $user->publicName() }}</p>
                                <p class="text-xs text-stone-500">{{ $user->isAdmin() ? 'Administrator' : 'Club member' }}</p>
                            </div>

                            <x-dropdown-link :href="route('members.dashboard')">Dashboard</x-dropdown-link>
                            <x-dropdown-link :href="route('members.posts.index')">My posts</x-dropdown-link>
                            <x-dropdown-link :href="route('members.posts.create')">Write a post</x-dropdown-link>

                            @if ($user->isAdmin())
                                <div class="my-1 border-t border-stone-100"></div>
                                <x-dropdown-link :href="route('members.enquiries.index')">
                                    Enquiries
                                    @if ($unread = \App\Models\Enquiry::unread()->count())
                                        <span class="ml-auto rounded-full bg-red-100 px-1.5 py-0.5 text-xs font-bold text-red-700">{{ $unread }}</span>
                                    @endif
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('members.whitelist.index')">Members &amp; whitelist</x-dropdown-link>
                                <x-dropdown-link :href="route('members.pages.index')">Pages</x-dropdown-link>
                            @endif

                            <div class="my-1 border-t border-stone-100"></div>
                            <x-dropdown-link :href="route('members.profile')">My profile</x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center px-4 py-2 text-left text-sm text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-club-100 transition-colors hover:bg-club-800 md:block">
                        Members' login
                    </a>
                @endauth

                {{-- Mobile menu button --}}
                <button
                    type="button"
                    data-nav-toggle
                    aria-expanded="false"
                    aria-controls="mobile-nav"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-club-100 transition-colors hover:bg-club-800 md:hidden"
                >
                    <span class="sr-only">Menu</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile navigation panel --}}
    <div id="mobile-nav" data-nav-panel hidden class="border-t border-club-800 bg-club-900 md:hidden">
        <nav class="mx-auto max-w-6xl space-y-1 px-4 py-4 sm:px-6" aria-label="Mobile">
            <x-mobile-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-mobile-nav-link>
            <x-mobile-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">Blog</x-mobile-nav-link>

            @foreach ($navPages as $page)
                <x-mobile-nav-link :href="route('pages.show', $page)">{{ $page->title }}</x-mobile-nav-link>
            @endforeach

            <x-mobile-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-mobile-nav-link>

            <div class="mt-4 border-t border-club-800 pt-4">
                @auth
                    <p class="px-3 pb-2 text-xs font-semibold tracking-wider text-club-400 uppercase">
                        {{ $user->publicName() }} — {{ $user->isAdmin() ? 'Administrator' : 'Member' }}
                    </p>
                    <x-mobile-nav-link :href="route('members.dashboard')">Dashboard</x-mobile-nav-link>
                    <x-mobile-nav-link :href="route('members.posts.create')">Write a post</x-mobile-nav-link>
                    <x-mobile-nav-link :href="route('members.posts.index')">My posts</x-mobile-nav-link>

                    @if ($user->isAdmin())
                        <x-mobile-nav-link :href="route('members.enquiries.index')">Enquiries</x-mobile-nav-link>
                        <x-mobile-nav-link :href="route('members.whitelist.index')">Members &amp; whitelist</x-mobile-nav-link>
                        <x-mobile-nav-link :href="route('members.pages.index')">Pages</x-mobile-nav-link>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-base font-medium text-club-100 transition-colors hover:bg-club-800">
                            Sign out
                        </button>
                    </form>
                @else
                    <x-mobile-nav-link :href="route('login')">Members' login</x-mobile-nav-link>
                @endauth
            </div>
        </nav>
    </div>
</header>
