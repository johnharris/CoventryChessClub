@extends('layouts.members')

@section('title', 'Posts')

@section('members')
    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">
                {{ $isAdmin ? 'All posts' : 'My posts' }}
            </h1>
            <p class="mt-1.5 text-stone-600">
                {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
                {{ $isAdmin ? 'across the whole site' : 'written by you' }}.
            </p>
        </div>

        <form method="GET" action="{{ route('members.posts.index') }}" class="flex gap-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $query }}" placeholder="Search…" class="field-input !py-2">
            <button type="submit" class="btn-secondary !py-2 text-xs">Search</button>
        </form>
    </header>

    @if ($posts->isEmpty())
        <div class="card p-10 text-center">
            <h2 class="font-semibold text-stone-900">No posts found</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-stone-600">
                {{ $query ? 'Nothing matches that search.' : 'Nothing written yet — start with your first post.' }}
            </p>
            <a href="{{ route('members.posts.create') }}" class="btn-primary mt-5">Write a post</a>
        </div>
    @else
        <div class="card overflow-hidden">
            {{-- Desktop table --}}
            <table class="hidden w-full text-left text-sm sm:table">
                <thead class="border-b border-stone-200 bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-stone-700">Title</th>
                        <th class="px-4 py-3 font-semibold text-stone-700">Type</th>
                        @if ($isAdmin)
                            <th class="px-4 py-3 font-semibold text-stone-700">Author</th>
                        @endif
                        <th class="px-4 py-3 font-semibold text-stone-700">Status</th>
                        <th class="px-4 py-3 font-semibold text-stone-700">Updated</th>
                        <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($posts as $post)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('members.posts.edit', $post) }}" class="font-medium text-stone-900 hover:text-club-700">
                                    {{ $post->title }}
                                </a>
                                @if ($post->is_featured)
                                    <span class="badge ml-1.5 bg-amber-100 text-amber-800">Featured</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-600">{{ $post->badge() }}</td>
                            @if ($isAdmin)
                                <td class="px-4 py-3 text-stone-600">{{ $post->user?->publicName() }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <span class="badge {{ $post->is_published ? 'bg-club-100 text-club-800' : 'bg-stone-200 text-stone-700' }}">
                                    {{ $post->is_published ? 'Live' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $post->updated_at->format('j M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('members.posts.edit', $post) }}" class="font-semibold text-club-700 hover:text-club-900">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Mobile list --}}
            <div class="divide-y divide-stone-100 sm:hidden">
                @foreach ($posts as $post)
                    <a href="{{ route('members.posts.edit', $post) }}" class="block p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-medium text-stone-900">{{ $post->title }}</p>
                            <span class="badge shrink-0 {{ $post->is_published ? 'bg-club-100 text-club-800' : 'bg-stone-200 text-stone-700' }}">
                                {{ $post->is_published ? 'Live' : 'Draft' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-stone-500">
                            {{ $post->badge() }}
                            @if ($isAdmin) · {{ $post->user?->publicName() }} @endif
                            · {{ $post->updated_at->format('j M Y') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-6">{{ $posts->links() }}</div>
    @endif
@endsection
