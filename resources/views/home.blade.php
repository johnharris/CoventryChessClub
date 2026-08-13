@extends('layouts.app')

@section('content')
    @php
        $meeting = config('club.meeting');
        $venue = config('club.venue');
        $juniorsVenue = config('club.juniors_venue');
    @endphp

    {{-- ================= Hero ================= --}}
    <section class="relative overflow-hidden bg-club-900 text-white">
        {{-- Decorative board pattern --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]" aria-hidden="true"
             style="background-image:
                linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%),
                linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%);
                background-size: 96px 96px; background-position: 0 0, 48px 48px;"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:py-24">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-club-800/80 px-3 py-1 text-xs font-semibold tracking-wider text-club-200 uppercase ring-1 ring-club-700">
                        Established club · Coventry
                    </p>

                    <h1 class="mt-5 text-4xl leading-[1.1] font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                        Chess in Coventry, every {{ $meeting['day'] }} evening
                    </h1>

                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-club-200">
                        Whether you have just learned the moves or you have played league chess for
                        decades, there is a board and a game waiting for you at
                        {{ $venue['name'] }} from {{ $meeting['time'] }}.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('contact') }}" class="btn bg-white text-club-900 hover:bg-club-50">
                            Visit the club
                        </a>
                        <a href="{{ route('posts.index') }}" class="btn bg-club-800 text-white ring-1 ring-club-700 ring-inset hover:bg-club-700">
                            Read the blog
                        </a>
                    </div>

                    <dl class="mt-10 grid max-w-lg grid-cols-3 gap-6 border-t border-club-800 pt-6">
                        <div>
                            <dt class="text-xs font-medium tracking-wider text-club-400 uppercase">Club night</dt>
                            <dd class="mt-1 text-sm font-semibold text-white">{{ $meeting['day'] }}s, {{ $meeting['time'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wider text-club-400 uppercase">Leagues</dt>
                            <dd class="mt-1 text-sm font-semibold text-white">Coventry &amp; Leamington</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wider text-club-400 uppercase">Juniors</dt>
                            <dd class="mt-1 text-sm font-semibold text-white">Weekly section</dd>
                        </div>
                    </dl>
                </div>

                {{-- A live board in the hero: an actual position, not a picture --}}
                <div class="mx-auto w-full max-w-md lg:max-w-none">
                    <div class="rounded-2xl bg-club-950/60 p-4 ring-1 ring-club-800 sm:p-6">
                        @include('partials.static-board', [
                            'fen' => 'r1bqk2r/pppp1ppp/2n2n2/2b1p3/2B1P3/2NP1N2/PPP2PPP/R1BQK2R w KQkq - 4 6',
                            'orientation' => 'white',
                        ])
                        <p class="mt-3 text-center text-sm text-club-300">
                            The Italian Game — one of the openings you will meet on a club night
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= Featured post ================= --}}
    @if ($featured)
        <section class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-2xl font-bold tracking-tight text-stone-900">Latest from the club</h2>
                <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-club-700 hover:text-club-900">All posts</a>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <article class="card overflow-hidden">
                        @if ($featured->type === \App\Models\Post::TYPE_POSITION && $featured->fen)
                            <div class="grid gap-0 sm:grid-cols-[14rem_minmax(0,1fr)]">
                                <div class="bg-stone-50 p-5">
                                    @include('partials.static-board', [
                                        'fen' => $featured->fen,
                                        'orientation' => $featured->orientation,
                                        'coordinates' => false,
                                    ])
                                </div>
                                <div class="p-6">
                                    @include('partials.featured-body', ['post' => $featured])
                                </div>
                            </div>
                        @else
                            <div class="p-6 sm:p-8">
                                @include('partials.featured-body', ['post' => $featured])
                            </div>
                        @endif
                    </article>
                </div>

                <div class="space-y-4">
                    @forelse ($latest as $post)
                        <a href="{{ route('posts.show', $post) }}" class="card group block p-4 transition-shadow hover:shadow-md">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-semibold tracking-wide text-club-700 uppercase">{{ $post->badge() }}</span>
                                <span class="text-stone-400">·</span>
                                <time class="text-stone-500">{{ $post->displayDate() }}</time>
                            </div>
                            <h3 class="mt-1.5 leading-snug font-semibold text-stone-900 transition-colors group-hover:text-club-700">
                                {{ $post->title }}
                            </h3>
                            <p class="mt-1 text-sm text-stone-600">{{ $post->preview(16) }}</p>
                        </a>
                    @empty
                        <p class="card p-5 text-sm text-stone-500">
                            No other posts yet. Club members can sign in and add the first one.
                        </p>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    {{-- ================= Chess content ================= --}}
    @if ($chessPosts->isNotEmpty())
        <section class="border-y border-stone-200 bg-white py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex items-baseline justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-stone-900">Positions &amp; games</h2>
                        <p class="mt-1.5 text-stone-600">
                            Annotated games and puzzles from our players, on a proper board you can play through.
                        </p>
                    </div>
                    <a href="{{ route('posts.index', ['type' => 'game']) }}" class="hidden shrink-0 text-sm font-semibold text-club-700 hover:text-club-900 sm:block">
                        All games
                    </a>
                </div>

                <div class="mt-7 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($chessPosts as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= Join us ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="card p-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-club-100 text-club-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">New to the club?</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">
                    Just turn up on a {{ $meeting['day'] }} evening — there is no need to book, and
                    your first few visits are free. Bring nothing but yourself; we have the boards,
                    sets and clocks.
                </p>
                <a href="{{ route('contact') }}" class="mt-4 inline-block text-sm font-semibold text-club-700 hover:text-club-900">
                    Ask us a question
                </a>
            </div>

            <div class="card p-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-club-100 text-club-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">League chess</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">
                    We enter teams across the divisions of the Coventry &amp; District League, plus a
                    side in the Leamington League. If you fancy competitive play, there is a board
                    for you at whatever standard you are.
                </p>
            </div>

            <div class="card p-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-club-100 text-club-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">Junior section</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">
                    Our junior section runs every {{ $meeting['juniors'] }} at {{ $juniorsVenue['name'] }},
                    {{ $juniorsVenue['address'] }} — a separate venue from the main club night. Places fill
                    up very quickly and must be booked in advance, so please check with us before
                    attending.@if (! empty($juniorsVenue['fee'])) Sessions are {{ $juniorsVenue['fee'] }}.@endif
                </p>
                <a href="{{ route('contact') }}" class="mt-4 inline-block text-sm font-semibold text-club-700 hover:text-club-900">
                    Enquire about juniors
                </a>
            </div>
        </div>
    </section>

    {{-- ================= Where to find us ================= --}}
    <section class="border-t border-stone-200 bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-stone-900">Where to find us</h2>
                    <p class="mt-3 leading-relaxed text-stone-600">
                        We meet at {{ $venue['name'] }} on {{ $venue['address'] }}, which kindly
                        provides our venue every week. There is parking on site and a bar.
                    </p>

                    <address class="mt-5 space-y-1 text-stone-700 not-italic">
                        <p class="font-semibold text-stone-900">{{ $venue['name'] }}</p>
                        <p>{{ $venue['address'] }}</p>
                        <p>{{ $venue['postcode'] }}</p>
                        @if (! empty($venue['entrance']))
                            <p class="font-medium">{{ $venue['entrance'] }}</p>
                        @endif
                    </address>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($venue['map_url'])
                            <a href="{{ $venue['map_url'] }}" target="_blank" rel="noopener" class="btn-primary">Open in maps</a>
                        @endif
                        <a href="{{ route('contact') }}" class="btn-secondary">Contact the club</a>
                    </div>
                </div>

                <div class="rounded-2xl bg-club-50 p-6 ring-1 ring-club-100 sm:p-8">
                    <h3 class="text-sm font-semibold tracking-wider text-club-800 uppercase">A typical club night</h3>
                    <ul class="mt-4 space-y-3.5 text-sm text-stone-700">
                        <li class="flex gap-3">
                            <span class="mt-0.5 font-mono text-xs font-semibold text-club-600">7:30pm</span>
                            <span>Club opens for the evening. League matches begin, and anyone not playing a fixture gets a friendly game.</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-0.5 font-mono text-xs font-semibold text-club-600">Later</span>
                            <span>Post-mortems at the bar — usually where the most instructive chess of the night happens.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection
