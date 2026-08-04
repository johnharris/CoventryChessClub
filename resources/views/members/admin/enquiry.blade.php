@extends('layouts.members')

@section('title', 'Enquiry from '.$enquiry->name)

@section('members')
    <nav class="mb-5 text-sm">
        <a href="{{ route('members.enquiries.index') }}" class="font-medium text-club-700 hover:text-club-900">
            &larr; Back to enquiries
        </a>
    </nav>

    <div class="card p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-stone-900">
                    {{ $enquiry->subject ?: $enquiry->typeLabel() }}
                </h1>
                <p class="mt-1.5 text-sm text-stone-600">
                    From <strong class="font-semibold text-stone-900">{{ $enquiry->name }}</strong>
                    · {{ $enquiry->created_at->format('j F Y \a\t H:i') }}
                </p>
            </div>
            <span class="badge bg-stone-200 text-stone-700">{{ $enquiry->typeLabel() }}</span>
        </div>

        <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Email</dt>
                <dd class="mt-1">
                    <a href="mailto:{{ $enquiry->email }}" class="font-medium text-club-700 hover:text-club-900">
                        {{ $enquiry->email }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Telephone</dt>
                <dd class="mt-1 text-stone-900">
                    @if ($enquiry->phone)
                        <a href="tel:{{ $enquiry->phone }}" class="font-medium text-club-700 hover:text-club-900">{{ $enquiry->phone }}</a>
                    @else
                        <span class="text-stone-500">Not given</span>
                    @endif
                </dd>
            </div>
        </dl>

        <div class="mt-6 rounded-lg bg-stone-50 p-5 ring-1 ring-stone-200">
            <p class="text-sm leading-relaxed whitespace-pre-line text-stone-800">{{ $enquiry->message }}</p>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-stone-200 pt-5">
            <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode('Re: '.($enquiry->subject ?: $enquiry->typeLabel())) }}"
               class="btn-primary">
                Reply by email
            </a>

            <form method="POST" action="{{ route('members.enquiries.update', $enquiry) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="is_read" value="1">
                <input type="hidden" name="is_archived" value="{{ $enquiry->is_archived ? 0 : 1 }}">
                <button type="submit" class="btn-secondary">
                    {{ $enquiry->is_archived ? 'Move back to inbox' : 'Archive' }}
                </button>
            </form>

            <form method="POST" action="{{ route('members.enquiries.destroy', $enquiry) }}"
                  data-confirm="Delete this enquiry permanently?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ghost !text-red-600 hover:!bg-red-50">Delete</button>
            </form>
        </div>

        <p class="mt-4 text-xs text-stone-400">
            Received from {{ $enquiry->ip_address ?: 'an unknown address' }}.
        </p>
    </div>
@endsection
