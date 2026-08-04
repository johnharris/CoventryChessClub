@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->preview(28))
@section('og_type', 'article')

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-12">

        {{-- ---------- Header ---------- --}}
        <header>
            <nav class="mb-6 text-sm" aria-label="Breadcrumb">
                <a href="{{ route('posts.index') }}" class="font-medium text-club-700 hover:text-club-900">
                    &larr; Back to the blog
                </a>
            </nav>

            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="badge {{ match ($post->type) {
                    \App\Models\Post::TYPE_POSITION => 'bg-amber-100 text-amber-800',
                    \App\Models\Post::TYPE_GAME => 'bg-club-100 text-club-800',
                    default => 'bg-stone-200 text-stone-700',
                } }}">{{ $post->badge() }}</span>

                <time datetime="{{ ($post->published_at ?? $post->created_at)->toDateString() }}" class="text-stone-500">
                    {{ $post->displayDate() }}
                </time>

                @unless ($post->is_published)
                    <span class="badge bg-red-100 text-red-700">Draft — only visible to you</span>
                @endunless
            </div>

            <h1 class="mt-3 text-3xl leading-[1.15] font-bold tracking-tight text-balance text-stone-900 sm:text-4xl">
                {{ $post->title }}
            </h1>

            <div class="mt-4 flex items-center gap-3 border-b border-stone-200 pb-6">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-club-100 text-sm font-bold text-club-800">
                    {{ $post->user?->initials() ?? '?' }}
                </span>
                <div class="text-sm">
                    <p class="font-semibold text-stone-900">{{ $post->user?->publicName() ?? 'Coventry Chess Club' }}</p>
                    <p class="text-stone-500">
                        {{ $post->user?->isAdmin() ? 'Club administrator' : 'Club member' }}
                        @if ($post->user?->ecf_rating)
                            · ECF {{ $post->user->ecf_rating }}
                        @endif
                    </p>
                </div>

                @can('update', $post)
                    <a href="{{ route('members.posts.edit', $post) }}" class="no-print btn-secondary ml-auto shrink-0 !py-2 text-xs">
                        Edit this post
                    </a>
                @endcan
            </div>
        </header>

        {{-- ---------- Chess: a single position ---------- --}}
        @if ($post->type === \App\Models\Post::TYPE_POSITION && $post->fen)
            <section class="mt-8" aria-label="Chess position">
                <div class="grid gap-6 sm:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] sm:items-start">
                    <div>
                        @include('partials.static-board', [
                            'fen' => $post->fen,
                            'orientation' => $post->orientation,
                        ])
                    </div>

                    <div class="card p-5">
                        <p class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Position</p>
                        <p class="mt-2 text-lg font-semibold text-stone-900">
                            {{ $post->side_to_move === 'b' ? 'Black' : 'White' }} to move
                        </p>

                        @if ($post->caption)
                            <p class="mt-2 leading-relaxed text-stone-700">{{ $post->caption }}</p>
                        @endif

                        @if ($post->solution)
                            <details class="mt-4 rounded-lg bg-club-50 p-3.5 ring-1 ring-club-100">
                                <summary class="cursor-pointer text-sm font-semibold text-club-800 hover:text-club-900">
                                    Show the answer
                                </summary>
                                <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $post->solution }}</p>
                            </details>
                        @endif

                        <div class="mt-4 border-t border-stone-200 pt-3.5">
                            <label for="post-fen" class="mb-1.5 block text-xs font-semibold tracking-wider text-stone-500 uppercase">
                                FEN
                            </label>
                            <div class="flex gap-2">
                                <input
                                    id="post-fen"
                                    type="text"
                                    readonly
                                    value="{{ $post->fen }}"
                                    class="w-full rounded-lg bg-stone-50 px-2.5 py-1.5 font-mono text-xs text-stone-600 ring-1 ring-stone-300 ring-inset"
                                >
                                <button type="button" data-copy="#post-fen" class="no-print btn-secondary shrink-0 !px-3 !py-1.5 text-xs">
                                    Copy
                                </button>
                            </div>
                            <a
                                href="https://lichess.org/analysis/{{ str_replace(' ', '_', $post->fen) }}"
                                target="_blank"
                                rel="noopener"
                                class="no-print mt-3 inline-block text-sm font-medium text-club-700 hover:text-club-900"
                            >
                                Open in the lichess analysis board &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ---------- Chess: an annotated game ---------- --}}
        @if ($post->type === \App\Models\Post::TYPE_GAME && $post->pgn)
            <section class="mt-8" aria-label="Annotated game">
                {{-- Game details --}}
                <div class="card mb-5 p-5">
                    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="text-xs font-semibold tracking-wider text-stone-500 uppercase">White</dt>
                            <dd class="mt-1 font-semibold text-stone-900">{{ $post->white_player ?: 'Unknown' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Black</dt>
                            <dd class="mt-1 font-semibold text-stone-900">{{ $post->black_player ?: 'Unknown' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Result</dt>
                            <dd class="mt-1 font-semibold text-stone-900">
                                {{ $post->result && $post->result !== '*' ? $post->result : 'Unfinished' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Event</dt>
                            <dd class="mt-1 text-stone-900">
                                {{ $post->event ?: '—' }}
                                @if ($post->played_on)
                                    <span class="block text-sm text-stone-500">{{ $post->played_on->format('j M Y') }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                @include('partials.game-viewer', [
                    'pgn' => $post->pgn,
                    'orientation' => $post->orientation,
                    'moveText' => $game['moveText'] ?? null,
                ])

                <div class="no-print mt-4 space-y-3 text-sm">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <a
                            href="https://lichess.org/paste"
                            target="_blank"
                            rel="noopener"
                            class="font-medium text-club-700 hover:text-club-900"
                        >
                            Analyse this game on lichess &rarr;
                        </a>
                        <button type="button" data-copy="#raw-pgn" class="font-medium text-club-700 hover:text-club-900">
                            Copy the PGN
                        </button>
                    </div>

                    <details>
                        <summary class="cursor-pointer font-medium text-stone-600 hover:text-stone-900">
                            Show the raw PGN
                        </summary>
                        <pre id="raw-pgn" class="mt-2 overflow-x-auto rounded-lg bg-stone-900 p-4 text-xs leading-relaxed text-stone-100">{{ $post->pgn }}</pre>
                    </details>
                </div>
            </section>
        @endif

        {{-- ---------- Body ---------- --}}
        @if (filled($post->body))
            <div class="prose-club mt-9">
                {!! \App\Support\Markdown::toHtml($post->body) !!}
            </div>
        @endif

        {{-- ---------- Related ---------- --}}
        @if ($related->isNotEmpty())
            <aside class="no-print mt-14 border-t border-stone-200 pt-8">
                <h2 class="text-lg font-semibold text-stone-900">
                    {{ $post->isChessPost() ? 'More chess posts' : 'More from the club' }}
                </h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-3">
                    @foreach ($related as $other)
                        <a href="{{ route('posts.show', $other) }}" class="card group block p-4 transition-shadow hover:shadow-md">
                            <span class="text-xs font-semibold tracking-wide text-club-700 uppercase">{{ $other->badge() }}</span>
                            <h3 class="mt-1.5 text-sm leading-snug font-semibold text-stone-900 transition-colors group-hover:text-club-700">
                                {{ $other->title }}
                            </h3>
                            <time class="mt-1 block text-xs text-stone-500">{{ $other->displayDate() }}</time>
                        </a>
                    @endforeach
                </div>
            </aside>
        @endif
    </article>
@endsection
