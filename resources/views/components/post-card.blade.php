@props(['post', 'showBoard' => true])

@php
    $badgeClass = match ($post->type) {
        \App\Models\Post::TYPE_POSITION => 'bg-amber-100 text-amber-800',
        \App\Models\Post::TYPE_GAME => 'bg-club-100 text-club-800',
        default => 'bg-stone-200 text-stone-700',
    };
@endphp

<article class="card group flex flex-col overflow-hidden transition-shadow hover:shadow-md">
    @if ($showBoard && $post->type === \App\Models\Post::TYPE_POSITION && $post->fen)
        <div class="border-b border-stone-100 bg-stone-50 p-4">
            <div class="mx-auto max-w-56">
                @include('partials.static-board', [
                    'fen' => $post->fen,
                    'orientation' => $post->orientation,
                    'coordinates' => false,
                ])
            </div>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="badge {{ $badgeClass }}">{{ $post->badge() }}</span>
            <time datetime="{{ ($post->published_at ?? $post->created_at)->toDateString() }}" class="text-stone-500">
                {{ $post->displayDate() }}
            </time>
            @unless ($post->is_published)
                <span class="badge bg-red-100 text-red-700">Draft</span>
            @endunless
        </div>

        <h3 class="mt-2.5 text-lg leading-snug font-semibold tracking-tight text-stone-900">
            <a href="{{ route('posts.show', $post) }}" class="transition-colors group-hover:text-club-700">
                {{ $post->title }}
            </a>
        </h3>

        @if ($post->type === \App\Models\Post::TYPE_GAME && $post->matchup())
            <p class="mt-1.5 text-sm font-medium text-stone-600">{{ $post->matchup() }}</p>
            @if ($post->event)
                <p class="text-sm text-stone-500">{{ $post->event }}</p>
            @endif
        @endif

        <p class="mt-2.5 flex-1 text-sm leading-relaxed text-stone-600">{{ $post->preview(26) }}</p>

        <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-3 text-sm">
            <span class="text-stone-500">{{ $post->user?->publicName() ?? 'Club' }}</span>
            <a href="{{ route('posts.show', $post) }}" class="inline-flex items-center gap-1 font-semibold text-club-700 transition-colors hover:text-club-900">
                Read
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</article>
