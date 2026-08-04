@extends('layouts.members')

@section('title', 'Pages')

@section('members')
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Pages</h1>
            <p class="mt-1.5 max-w-2xl text-stone-600">
                Standing pages such as Fixtures, Teams and Coaching. Pages ticked to show in the
                navigation appear in the site menu, ordered by their position number.
            </p>
        </div>
        <a href="{{ route('members.pages.create') }}" class="btn-primary">New page</a>
    </header>

    @if ($pages->isEmpty())
        <div class="card p-10 text-center">
            <h2 class="font-semibold text-stone-900">No pages yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-stone-600">
                Create pages for the information that does not change week to week.
            </p>
            <a href="{{ route('members.pages.create') }}" class="btn-primary mt-5">Create the first page</a>
        </div>
    @else
        <div class="card divide-y divide-stone-100">
            @foreach ($pages as $page)
                <div class="flex flex-wrap items-center gap-3 p-4">
                    <span class="w-9 shrink-0 text-center text-sm font-medium text-stone-400 tabular-nums">
                        {{ $page->nav_order }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-stone-900">{{ $page->title }}</p>
                        <p class="text-sm text-stone-500">/{{ $page->slug }} · updated {{ $page->updated_at->format('j M Y') }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge {{ $page->is_published ? 'bg-club-100 text-club-800' : 'bg-stone-200 text-stone-700' }}">
                            {{ $page->is_published ? 'Live' : 'Hidden' }}
                        </span>
                        @if ($page->show_in_nav)
                            <span class="badge bg-stone-100 text-stone-600">In menu</span>
                        @endif
                        @if ($page->is_published)
                            <a href="{{ route('pages.show', $page) }}" class="btn-ghost !py-1.5 text-xs">View</a>
                        @endif
                        <a href="{{ route('members.pages.edit', $page) }}" class="btn-secondary !py-1.5 text-xs">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $pages->links() }}</div>
    @endif
@endsection
