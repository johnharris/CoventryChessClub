@extends('layouts.members')

@section('title', 'Enquiries')

@section('members')
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">
                {{ $archived ? 'Archived enquiries' : 'Enquiries' }}
            </h1>
            <p class="mt-1.5 text-stone-600">
                Messages sent through the contact form.
                @if (! config('club.enquiry_email'))
                    They are stored here only, because no notification address has been configured yet.
                @endif
            </p>
        </div>

        <nav class="flex gap-2">
            <x-filter-pill :href="route('members.enquiries.index')" :active="! $archived">
                Inbox
                @if ($unreadCount)
                    <span class="ml-1 rounded-full bg-white/25 px-1.5 text-xs">{{ $unreadCount }}</span>
                @endif
            </x-filter-pill>
            <x-filter-pill :href="route('members.enquiries.index', ['archived' => 1])" :active="$archived">
                Archived
            </x-filter-pill>
        </nav>
    </header>

    @if ($enquiries->isEmpty())
        <div class="card p-10 text-center">
            <h2 class="font-semibold text-stone-900">{{ $archived ? 'Nothing archived' : 'No enquiries yet' }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-stone-600">
                Messages from the <a href="{{ route('contact') }}" class="font-medium text-club-700 hover:text-club-900">contact page</a>
                will appear here.
            </p>
        </div>
    @else
        <div class="card divide-y divide-stone-100">
            @foreach ($enquiries as $enquiry)
                <a href="{{ route('members.enquiries.show', $enquiry) }}"
                   class="block p-4 transition-colors hover:bg-stone-50 {{ $enquiry->is_read ? '' : 'bg-club-50/40' }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @unless ($enquiry->is_read)
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-club-600" aria-label="Unread"></span>
                                @endunless
                                <p class="font-semibold text-stone-900">{{ $enquiry->name }}</p>
                                <span class="badge bg-stone-200 text-stone-700">{{ $enquiry->typeLabel() }}</span>
                                @if ($enquiry->strengthLabel())
                                    <span class="badge bg-club-100 text-club-800">{{ $enquiry->strengthLabel() }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-stone-600">
                                {{ $enquiry->subject ?: Str::limit($enquiry->message, 90) }}
                            </p>
                            <p class="mt-1 text-xs text-stone-500">{{ $enquiry->email }}</p>
                        </div>
                        <time class="shrink-0 text-sm text-stone-500">{{ $enquiry->created_at->format('j M Y, H:i') }}</time>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $enquiries->links() }}</div>
    @endif
@endsection
