@props(['post'])

<div class="flex flex-wrap items-center gap-2 text-xs">
    <span class="badge bg-club-100 text-club-800">{{ $post->badge() }}</span>
    <time datetime="{{ ($post->published_at ?? $post->created_at)->toDateString() }}" class="text-stone-500">
        {{ $post->displayDate() }}
    </time>
    <span class="text-stone-400">·</span>
    <span class="text-stone-500">{{ $post->user?->publicName() ?? 'Club' }}</span>
</div>

<h3 class="mt-3 text-2xl leading-tight font-bold tracking-tight text-stone-900 sm:text-3xl">
    <a href="{{ route('posts.show', $post) }}" class="hover:text-club-700">{{ $post->title }}</a>
</h3>

@if ($post->type === \App\Models\Post::TYPE_GAME && $post->matchup())
    <p class="mt-2 font-medium text-stone-700">{{ $post->matchup() }}</p>
@endif

<p class="mt-3 leading-relaxed text-stone-600">{{ $post->preview(44) }}</p>

<a href="{{ route('posts.show', $post) }}" class="mt-5 inline-flex items-center gap-1.5 font-semibold text-club-700 hover:text-club-900">
    Read the full post
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
    </svg>
</a>
