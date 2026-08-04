@extends('layouts.members')

@section('title', 'Dashboard')

@section('members')
    @php $user = auth()->user(); @endphp

    <header>
        <h1 class="text-2xl font-bold tracking-tight text-stone-900">
            Hello, {{ $user->publicName() }}
        </h1>
        <p class="mt-1.5 text-stone-600">
            You are signed in as {{ $user->isAdmin() ? 'a club administrator' : 'a club member' }}.
            @if ($user->isAdmin())
                You can publish and edit any post, manage the whitelist and edit the club's pages.
            @else
                You can write posts and edit or delete your own.
            @endif
        </p>
    </header>

    {{-- ---------- Stats ---------- --}}
    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-5">
            <p class="text-xs font-semibold tracking-wider text-stone-500 uppercase">My posts</p>
            <p class="mt-1.5 text-3xl font-bold text-stone-900">{{ $myPosts }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold tracking-wider text-stone-500 uppercase">My drafts</p>
            <p class="mt-1.5 text-3xl font-bold text-stone-900">{{ $myDrafts }}</p>
        </div>

        @if ($user->isAdmin())
            <a href="{{ route('members.enquiries.index') }}" class="card p-5 transition-shadow hover:shadow-md">
                <p class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Unread enquiries</p>
                <p class="mt-1.5 text-3xl font-bold {{ $unreadEnquiries ? 'text-red-600' : 'text-stone-900' }}">
                    {{ $unreadEnquiries }}
                </p>
            </a>
            <a href="{{ route('members.whitelist.index') }}" class="card p-5 transition-shadow hover:shadow-md">
                <p class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Active members</p>
                <p class="mt-1.5 text-3xl font-bold text-stone-900">{{ $memberCount }}</p>
                @if ($pendingInvites)
                    <p class="mt-1 text-xs text-stone-500">{{ $pendingInvites }} invitation{{ $pendingInvites === 1 ? '' : 's' }} not yet used</p>
                @endif
            </a>
        @endif
    </div>

    {{-- ---------- Quick actions ---------- --}}
    <section class="mt-9">
        <h2 class="text-lg font-semibold text-stone-900">Write something</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <a href="{{ route('members.posts.create', ['type' => 'general']) }}" class="card group p-5 transition-shadow hover:shadow-md">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-stone-100 text-stone-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z" />
                    </svg>
                </div>
                <h3 class="mt-3 font-semibold text-stone-900 group-hover:text-club-700">General post</h3>
                <p class="mt-1 text-sm text-stone-600">Club news, fixtures, results or an article.</p>
            </a>

            <a href="{{ route('members.posts.create', ['type' => 'position']) }}" class="card group p-5 transition-shadow hover:shadow-md">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h16.5v16.5H3.75zM3.75 9h16.5M3.75 14.25h16.5M9 3.75v16.5M14.25 3.75v16.5" />
                    </svg>
                </div>
                <h3 class="mt-3 font-semibold text-stone-900 group-hover:text-club-700">Chess position</h3>
                <p class="mt-1 text-sm text-stone-600">A diagram from a FEN — puzzles and study positions.</p>
            </a>

            <a href="{{ route('members.posts.create', ['type' => 'game']) }}" class="card group p-5 transition-shadow hover:shadow-md">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-club-100 text-club-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                    </svg>
                </div>
                <h3 class="mt-3 font-semibold text-stone-900 group-hover:text-club-700">Annotated game</h3>
                <p class="mt-1 text-sm text-stone-600">Paste a PGN or drop in a .pgn file.</p>
            </a>
        </div>
    </section>

    {{-- ---------- Recent ---------- --}}
    <section class="mt-9">
        <div class="flex items-baseline justify-between">
            <h2 class="text-lg font-semibold text-stone-900">
                {{ $user->isAdmin() ? 'Recently edited' : 'My recent posts' }}
            </h2>
            <a href="{{ route('members.posts.index') }}" class="text-sm font-semibold text-club-700 hover:text-club-900">
                See all
            </a>
        </div>

        @if ($recent->isEmpty())
            <p class="card mt-4 p-6 text-stone-600">
                Nothing written yet. Use one of the buttons above to create your first post.
            </p>
        @else
            <div class="card mt-4 divide-y divide-stone-100">
                @foreach ($recent as $post)
                    <div class="flex flex-wrap items-center gap-3 p-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-stone-900">{{ $post->title }}</p>
                            <p class="mt-0.5 text-sm text-stone-500">
                                {{ $post->badge() }}
                                @if ($user->isAdmin())
                                    · {{ $post->user?->publicName() }}
                                @endif
                                · edited {{ $post->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="badge {{ $post->is_published ? 'bg-club-100 text-club-800' : 'bg-stone-200 text-stone-700' }}">
                            {{ $post->is_published ? 'Live' : 'Draft' }}
                        </span>
                        <a href="{{ route('members.posts.edit', $post) }}" class="btn-secondary !py-1.5 text-xs">Edit</a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ---------- Help ---------- --}}
    <section class="mt-9 rounded-xl bg-club-50 p-6 ring-1 ring-club-100">
        <h2 class="font-semibold text-club-900">Adding a board inside any post</h2>
        <p class="mt-2 text-sm leading-relaxed text-stone-700">
            You can drop a diagram into the middle of any post — including a general news post —
            by adding a fenced block to the body:
        </p>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-stone-900 p-4 text-xs leading-relaxed text-stone-100">```fen
r1bqkbnr/pppp1ppp/2n5/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 5 4
Caption: How should Black meet the threat?
Orientation: black
Solution: 4...Nf6 keeps the balance.
```</pre>
        <p class="mt-2.5 text-sm text-stone-600">
            The <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Caption</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Orientation</code> and
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Solution</code> lines are all optional.
        </p>
    </section>
@endsection
