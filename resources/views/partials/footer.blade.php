@php
    $venue = config('club.venue');
    $meeting = config('club.meeting');
    $links = config('club.links');
    $footerPages = \App\Models\Page::published()->inNav()->get(['title', 'slug']);
@endphp

<footer class="no-print mt-16 border-t border-club-800/40 bg-club-950 text-club-200">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">

            <div class="lg:col-span-2">
                <div class="flex items-center gap-2.5">
                    <x-knight-mark class="h-7 w-7 text-club-300" />
                    <span class="text-lg font-bold tracking-tight text-white">{{ config('club.name') }}</span>
                </div>
                <p class="mt-3 max-w-md text-sm leading-relaxed text-club-300">
                    {{ config('club.tagline') }}. We play in the Coventry &amp; District League
                    and the Leamington &amp; District League, and run a separate junior section.
                </p>
                <a href="{{ route('contact') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-white hover:text-club-200">
                    Get in touch
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <div>
                <h2 class="text-xs font-semibold tracking-wider text-white uppercase">Club night</h2>
                <address class="mt-4 space-y-1 text-sm leading-relaxed text-club-300 not-italic">
                    <p class="font-medium text-club-100">Every {{ $meeting['day'] }}, from {{ $meeting['time'] }}</p>
                    <p>{{ $venue['name'] }}</p>
                    <p>{{ $venue['address'] }}</p>
                    <p>{{ $venue['postcode'] }}</p>
                        @if (! empty($venue['entrance']))
                            <p class="font-medium">{{ $venue['entrance'] }}</p>
                        @endif
                </address>
                @if ($venue['map_url'])
                    <a href="{{ $venue['map_url'] }}" target="_blank" rel="noopener" class="mt-3 inline-block text-sm font-medium text-club-200 underline decoration-club-600 underline-offset-2 hover:text-white">
                        Find us on the map
                    </a>
                @endif
            </div>

            <div>
                <h2 class="text-xs font-semibold tracking-wider text-white uppercase">Chess links</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($footerPages as $page)
                        <li><a href="{{ route('pages.show', $page) }}" class="text-club-300 transition-colors hover:text-white">{{ $page->title }}</a></li>
                    @endforeach
                    <li><a href="{{ $links['coventry_league'] }}" target="_blank" rel="noopener" class="text-club-300 transition-colors hover:text-white">Coventry &amp; District League</a></li>
                    <li><a href="{{ $links['leamington_league'] }}" target="_blank" rel="noopener" class="text-club-300 transition-colors hover:text-white">Leamington League</a></li>
                    <li><a href="{{ $links['ecf'] }}" target="_blank" rel="noopener" class="text-club-300 transition-colors hover:text-white">English Chess Federation</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-club-800/60 pt-6 text-xs text-club-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('club.name') }}. All rights reserved.</p>
            <p class="flex flex-wrap items-center gap-x-4 gap-y-1">
                <span>Boards by <a href="https://github.com/lichess-org/chessground" target="_blank" rel="noopener" class="underline underline-offset-2 hover:text-club-200">Chessground</a> (lichess)</span>
                @guest
                    <a href="{{ route('login') }}" class="underline underline-offset-2 hover:text-club-200">Members' login</a>
                @endguest
            </p>
        </div>
    </div>
</footer>
