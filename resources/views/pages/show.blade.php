@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <div class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-12">
            <h1 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">{{ $page->title }}</h1>
            <p class="mt-3 text-sm text-stone-500">Last updated {{ $page->updated_at->format('j F Y') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-12">
        <div class="prose-club">
            {!! \App\Support\Markdown::toHtml($page->body) !!}
        </div>

        @auth
            @if (auth()->user()->isAdmin())
                <div class="no-print mt-10 border-t border-stone-200 pt-6">
                    <a href="{{ route('members.pages.edit', $page) }}" class="btn-secondary">Edit this page</a>
                </div>
            @endif
        @endauth
    </div>
@endsection
