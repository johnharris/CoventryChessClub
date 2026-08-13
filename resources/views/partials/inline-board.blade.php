@props([
    'fen',
    'caption' => null,
    'orientation' => 'white',
    'solution' => null,
    'side_to_move' => 'w',
])

{{-- A diagram dropped into the middle of a post body via a ```fen block. --}}
<figure class="not-prose my-7">
    <div class="mx-auto w-full max-w-sm">
        @include('partials.static-board', [
            'fen' => $fen,
            'orientation' => $orientation,
        ])
    </div>

    <figcaption class="mx-auto mt-3 max-w-sm text-center text-sm text-stone-600">
        <span class="font-medium text-stone-800">
            {{ ($side_to_move ?? 'w') === 'b' ? 'Black' : 'White' }} to move
        </span>
        @if ($caption)
            <span aria-hidden="true" class="mx-1 text-stone-400">·</span>{{ $caption }}
        @endif

    </figcaption>

    @if (filled($solution))
        <div class="mx-auto mt-3 max-w-sm text-left">
            <x-reveal-answer :solution="$solution" class="!mt-0" />
        </div>
    @endif
</figure>
