@extends('layouts.members')

@section('title', 'Images')

@section('members')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Images</h1>
            <p class="mt-1.5 text-sm text-stone-600">
                {{ auth()->user()->isAdmin()
                    ? 'Every photograph uploaded to the site.'
                    : 'The photographs you have uploaded.' }}
            </p>
        </div>
        <p class="text-sm text-stone-500">
            {{ $media->total() }} {{ Str::plural('image', $media->total()) }},
            {{ $diskUsage > 1048576 ? round($diskUsage / 1048576, 1).' MB' : round($diskUsage / 1024).' KB' }} used
        </p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl bg-green-50 p-4 text-sm text-green-900 ring-1 ring-green-200">
            {{ session('status') }}
        </div>
    @endif

    {{-- Upload straight from the library, for photographs to be used later. --}}
    <div class="card mt-6 p-5 sm:p-6" data-body-images>
        <h2 class="text-sm font-semibold text-stone-900">Add photographs</h2>
        <p class="mt-1.5 text-sm text-stone-600">
            Upload here to build up a library, or add them while writing a post.
            Large photographs are resized automatically.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <label class="btn-primary cursor-pointer text-sm">
                <span data-insert-label>Choose photographs</span>
                <input type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif"
                       class="hidden" data-library-file>
            </label>
            <span class="text-xs text-stone-500" data-insert-status aria-live="polite"></span>
        </div>
    </div>

    @if ($media->isEmpty())
        <div class="card mt-6 p-10 text-center">
            <p class="text-sm text-stone-600">No photographs yet.</p>
        </div>
    @else
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($media as $image)
                <div class="card overflow-hidden">
                    <a href="{{ $image->originalUrl() }}" target="_blank" rel="noopener"
                       class="block aspect-[3/2] bg-stone-100">
                        <img src="{{ $image->thumbUrl() }}" alt="{{ $image->alt_text }}"
                             class="h-full w-full object-cover" loading="lazy">
                    </a>

                    <div class="space-y-3 p-4">
                        <div>
                            <p class="truncate text-sm font-medium text-stone-900" title="{{ $image->original_name }}">
                                {{ $image->original_name }}
                            </p>
                            <p class="mt-0.5 text-xs text-stone-500">
                                {{ $image->width }}&times;{{ $image->height }} &middot; {{ $image->humanSize() }}
                                &middot; {{ $image->created_at->format('j M Y') }}
                                @if (auth()->user()->isAdmin() && $image->user)
                                    &middot; {{ $image->user->name }}
                                @endif
                            </p>
                        </div>

                        <form method="POST" action="{{ route('members.media.update', $image) }}" class="space-y-2">
                            @csrf
                            @method('PUT')
                            <label for="alt-{{ $image->id }}" class="block text-xs font-medium text-stone-700">
                                Description (read aloud by screen readers)
                            </label>
                            <input type="text" id="alt-{{ $image->id }}" name="alt_text" maxlength="255"
                                   value="{{ $image->alt_text }}" class="field-input !py-1.5 text-sm">
                            <button type="submit" class="btn-secondary !py-1.5 text-xs">Save description</button>
                        </form>

                        <div class="flex items-center justify-between gap-2 border-t border-stone-100 pt-3">
                            <button type="button" class="text-xs font-medium text-club-700 hover:text-club-900"
                                    data-copy-markdown="{{ $image->markdown() }}">
                                Copy Markdown
                            </button>
                            <form method="POST" action="{{ route('members.media.destroy', $image) }}"
                                  onsubmit="return confirm('Delete this photograph? Any post using it will show no image.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-700 hover:text-red-900">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $media->links() }}
        </div>
    @endif
@endsection
