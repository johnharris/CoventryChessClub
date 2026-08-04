@extends('layouts.members')

@section('title', 'Edit: '.$post->title)

@section('members')
    <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Edit post</h1>
            <p class="mt-1.5 text-stone-600">
                {{ $post->is_published ? 'This post is live' : 'This post is a draft' }}
                @if ($post->user_id !== auth()->id())
                    · written by {{ $post->user?->publicName() }}
                @endif
                · last saved {{ $post->updated_at->diffForHumans() }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('posts.show', $post) }}" class="btn-secondary !py-2 text-xs">
                {{ $post->is_published ? 'View on the site' : 'Preview' }}
            </a>
        </div>
    </header>

    <form method="POST" action="{{ route('members.posts.update', $post) }}">
        @csrf
        @method('PUT')

        @include('members.posts._form', ['post' => $post])

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-stone-200 pt-6">
            <button type="submit" class="btn-primary">Save changes</button>
            <a href="{{ route('members.posts.index') }}" class="btn-ghost">Back to posts</a>
        </div>
    </form>

    <div class="mt-8 rounded-xl bg-red-50 p-5 ring-1 ring-red-200">
        <h2 class="font-semibold text-red-900">Delete this post</h2>
        <p class="mt-1 text-sm text-red-800">This cannot be undone.</p>
        <form method="POST" action="{{ route('members.posts.destroy', $post) }}" class="mt-4"
              data-confirm="Delete “{{ $post->title }}” permanently?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger !py-2 text-xs">Delete post</button>
        </form>
    </div>
@endsection
