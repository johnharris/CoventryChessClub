@extends('layouts.members')

@section('title', 'Edit: '.$page->title)

@section('members')
    <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Edit page</h1>
            <p class="mt-1.5 text-stone-600">
                <code class="font-mono text-sm">/{{ $page->slug }}</code> ·
                last saved {{ $page->updated_at->diffForHumans() }}
            </p>
        </div>
        @if ($page->is_published)
            <a href="{{ route('pages.show', $page) }}" class="btn-secondary !py-2 text-xs">View on the site</a>
        @endif
    </header>

    <form method="POST" action="{{ route('members.pages.update', $page) }}">
        @csrf
        @method('PUT')

        @include('members.admin.pages._form', ['page' => $page])

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary">Save changes</button>
            <a href="{{ route('members.pages.index') }}" class="btn-ghost">Back to pages</a>
        </div>
    </form>

    <div class="mt-8 rounded-xl bg-red-50 p-5 ring-1 ring-red-200">
        <h2 class="font-semibold text-red-900">Delete this page</h2>
        <p class="mt-1 text-sm text-red-800">This cannot be undone.</p>
        <form method="POST" action="{{ route('members.pages.destroy', $page) }}" class="mt-4"
              data-confirm="Delete the page “{{ $page->title }}” permanently?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger !py-2 text-xs">Delete page</button>
        </form>
    </div>
@endsection
