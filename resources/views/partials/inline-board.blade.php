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

        @if ($solution)
            <details class="mt-2 text-left">
                <summary class="cursor-pointer text-center font-medium text-club-700 hover:text-club-800">
                    Show the answer
                </summary>
                <p class="mt-2 rounded-lg bg-club-50 px-3 py-2 text-stone-700">{{ $solution }}</p>
            </details>
        @endif
    </figcaption>
</figure>
