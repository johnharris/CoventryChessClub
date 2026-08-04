@extends('layouts.app')

@section('title', 'Contact us')
@section('description', 'Get in touch with Coventry Chess Club about visiting, joining, junior chess or coaching.')

@section('content')
    @php
        $venue = config('club.venue');
        $meeting = config('club.meeting');
    @endphp

    <div class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12">
            <h1 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Contact the club</h1>
            <p class="mt-3 max-w-2xl text-lg text-stone-600">
                Thinking of coming along, asking about the junior section, or arranging a match?
                Send us a message and one of the club officers will reply.
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">

            {{-- ---------- Form ---------- --}}
            <div>
                @if (session('status'))
                    <div class="mb-6 rounded-xl bg-club-50 p-5 ring-1 ring-club-200">
                        <p class="font-semibold text-club-900">Message sent</p>
                        <p class="mt-1 text-sm text-club-800">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 p-5 ring-1 ring-red-200">
                        <p class="font-semibold text-red-900">Please check the form</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="card p-6 sm:p-8">
                    @csrf

                    {{-- Honeypot: hidden from people, tempting to bots --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="field-label">Your name <span class="text-red-600">*</span></label>
                            <input id="name" name="name" type="text" required autocomplete="name"
                                   value="{{ old('name') }}" class="field-input">
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="field-label">Email address <span class="text-red-600">*</span></label>
                            <input id="email" name="email" type="email" required autocomplete="email"
                                   value="{{ old('email') }}" class="field-input">
                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="field-label">Telephone <span class="text-stone-400">(optional)</span></label>
                            <input id="phone" name="phone" type="tel" autocomplete="tel"
                                   value="{{ old('phone') }}" class="field-input">
                            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="enquiry_type" class="field-label">What is it about? <span class="text-red-600">*</span></label>
                            <select id="enquiry_type" name="enquiry_type" required class="field-input">
                                @foreach (\App\Models\Enquiry::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('enquiry_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('enquiry_type') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="subject" class="field-label">Subject <span class="text-stone-400">(optional)</span></label>
                            <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="field-input">
                            @error('subject') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="message" class="field-label">Message <span class="text-red-600">*</span></label>
                            <textarea id="message" name="message" rows="7" required class="field-input">{{ old('message') }}</textarea>
                            <p class="field-hint">
                                If you are asking about the junior section, it helps to mention the child's
                                age and any experience they already have.
                            </p>
                            @error('message') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-4 border-t border-stone-200 pt-5">
                        <button type="submit" class="btn-primary">Send message</button>
                        <p class="text-sm text-stone-500">
                            We use your details only to reply to this enquiry.
                        </p>
                    </div>
                </form>
            </div>

            {{-- ---------- Aside ---------- --}}
            <aside class="space-y-6">
                <div class="card p-6">
                    <h2 class="text-sm font-semibold tracking-wider text-stone-500 uppercase">Come and see us</h2>
                    <p class="mt-3 text-sm leading-relaxed text-stone-700">
                        You do not need to contact us first — just turn up on a
                        {{ $meeting['day'] }} evening from {{ $meeting['time'] }}.
                    </p>
                    <address class="mt-4 space-y-1 text-sm text-stone-700 not-italic">
                        <p class="font-semibold text-stone-900">{{ $venue['name'] }}</p>
                        <p>{{ $venue['address'] }}</p>
                        <p>{{ $venue['postcode'] }}</p>
                    </address>
                    @if ($venue['map_url'])
                        <a href="{{ $venue['map_url'] }}" target="_blank" rel="noopener"
                           class="mt-4 inline-block text-sm font-semibold text-club-700 hover:text-club-900">
                            Open in maps &rarr;
                        </a>
                    @endif
                </div>

                <div class="rounded-xl bg-club-50 p-6 ring-1 ring-club-100">
                    <h2 class="text-sm font-semibold tracking-wider text-club-800 uppercase">Junior section</h2>
                    <p class="mt-3 text-sm leading-relaxed text-stone-700">
                        Our junior section runs {{ $meeting['juniors'] }}. Places are limited, so please
                        get in touch before bringing a young player along for the first time.
                    </p>
                </div>

                <div class="card p-6">
                    <h2 class="text-sm font-semibold tracking-wider text-stone-500 uppercase">Club members</h2>
                    <p class="mt-3 text-sm leading-relaxed text-stone-700">
                        Already a member and want to write for the site?
                        <a href="{{ route('login') }}" class="font-semibold text-club-700 hover:text-club-900">Sign in here</a>.
                        Accounts are set up by the club administrators.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
