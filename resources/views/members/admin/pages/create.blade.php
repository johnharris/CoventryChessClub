@extends('layouts.members')

@section('title', 'New page')

@section('members')
    <header class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">New page</h1>
        <p class="mt-1.5 text-stone-600">For information that does not change week to week.</p>
    </header>

    <form method="POST" action="{{ route('members.pages.store') }}">
        @csrf

        @include('members.admin.pages._form')

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary">Create page</button>
            <a href="{{ route('members.pages.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
