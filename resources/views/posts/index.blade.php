@extends('layouts.app')

@section('title', 'Blog')
@section('description', 'News, annotated games and chess puzzles from Coventry Chess Club.')

@section('content')
    <div class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12">
            <h1 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Club blog</h1>
            <p class="mt-3 max-w-2xl text-lg text-stone-600">
                Club news and fixtures, annotated games from our players, and positions worth a
                second look — all on a board you can play through.
            </p>

            {{-- Filters and search --}}
            <div class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <nav class="flex flex-wrap gap-2" aria-label="Filter posts by type">
                    <x-filter-pill :href="route('posts.index', request()->only('q'))" :active="! $activeType">
                        Everything
                    </x-filter-pill>
                    @foreach (\App\Models\Post::TYPES as $value => $label)
                        <x-filter-pill
                            :href="route('posts.index', array_merge(request()->only('q'), ['type' => $value]))"
                            :active="$activeType === $value"
                        >
                            {{ match ($value) {
                                'general' => 'News',
                                'position' => 'Positions',
                                'game' => 'Games',
                                default => $label,
                            } }}
                        </x-filter-pill>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('posts.index') }}" class="flex gap-2 lg:w-80">
                    @if ($activeType)
                        <input type="hidden" name="type" value="{{ $activeType }}">
                    @endif
                    <label for="q" class="sr-only">Search posts</label>
                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Search posts and players…"
                        class="field-input"
                    >
                    <button type="submit" class="btn-secondary shrink-0">Search</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12">
        @if ($query)
            <p class="mb-6 text-sm text-stone-600">
                {{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for
                <strong class="font-semibold text-stone-900">“{{ $query }}”</strong>.
                <a href="{{ route('posts.index', $activeType ? ['type' => $activeType] : []) }}" class="ml-1 font-medium text-club-700 hover:text-club-900">Clear</a>
            </p>
        @endif

        @if ($posts->isEmpty())
            <div class="card p-10 text-center">
                <h2 class="text-lg font-semibold text-stone-900">Nothing here yet</h2>
                <p class="mx-auto mt-2 max-w-md text-stone-600">
                    @if ($query || $activeType)
                        No posts match that filter. Try clearing the search, or browse everything.
                    @else
                        The blog is ready and waiting. Club members can sign in and write the first post.
                    @endif
                </p>
                @if ($query || $activeType)
                    <a href="{{ route('posts.index') }}" class="btn-primary mt-5">Browse everything</a>
                @endif
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
