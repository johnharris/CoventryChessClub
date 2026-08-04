@extends('layouts.members')

@section('title', 'Write a post')

@section('members')
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">Write a post</h1>
        <p class="mt-1.5 text-stone-600">
            Choose the kind of post, fill in the details, and either save it as a draft or publish it.
        </p>
    </header>

    <form method="POST" action="{{ route('members.posts.store') }}">
        @csrf

        @include('members.posts._form', ['type' => $type])

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-stone-200 pt-6">
            <button type="submit" class="btn-primary">Save post</button>
            <a href="{{ route('members.posts.index') }}" class="btn-ghost">Cancel</a>
            <p class="text-sm text-stone-500">
                Tick “Publish this post” above to make it live straight away.
            </p>
        </div>
    </form>
@endsection
