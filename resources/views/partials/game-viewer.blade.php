@props([
    'pgn',
    'orientation' => 'white',
    'moveText' => null,
    'compact' => false,
])

{{--
    The annotated game viewer: a lichess-style board with a clickable move list.

    The PGN is embedded in a data attribute and the move text is also rendered as
    plain HTML below, so the game is readable (and indexable) before JavaScript
    upgrades it into the interactive viewer.
--}}
<div
    data-game-viewer
    data-orientation="{{ $orientation === 'black' ? 'black' : 'white' }}"
    data-pgn="{{ $pgn }}"
    class="not-prose overflow-hidden rounded-xl bg-white ring-1 ring-stone-900/5 focus:outline-none"
>
    <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_20rem]">

        {{-- Board --}}
        <div class="p-4 sm:p-5">
            <div data-viewer-board class="board-frame" data-board-pending="true"></div>

            {{-- Controls --}}
            <div class="mt-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1">
                    <button type="button" data-viewer-action="first" class="btn-icon" title="Starting position (Up arrow)">
                        <span class="sr-only">Starting position</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M15.28 5.22a.75.75 0 0 1 0 1.06L11.56 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"/><path d="M6.75 4.5a.75.75 0 0 1 .75.75v9.5a.75.75 0 0 1-1.5 0v-9.5a.75.75 0 0 1 .75-.75Z"/></svg>
                    </button>
                    <button type="button" data-viewer-action="prev" class="btn-icon" title="Previous move (Left arrow)">
                        <span class="sr-only">Previous move</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1 0 1.06L9.06 10l3.73 3.71a.75.75 0 1 1-1.06 1.06l-4.25-4.24a.75.75 0 0 1 0-1.06l4.25-4.24a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                    </button>
                    <button type="button" data-viewer-action="next" class="btn-icon" title="Next move (Right arrow)">
                        <span class="sr-only">Next move</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.29a.75.75 0 1 1 1.06-1.06l4.25 4.24a.75.75 0 0 1 0 1.06l-4.25 4.24a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                    </button>
                    <button type="button" data-viewer-action="last" class="btn-icon" title="Final position (Down arrow)">
                        <span class="sr-only">Final position</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4.72 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06L5.78 14.78a.75.75 0 1 1-1.06-1.06L8.44 10 4.72 6.28a.75.75 0 0 1 0-1.06Z"/><path d="M13.25 4.5a.75.75 0 0 1 .75.75v9.5a.75.75 0 0 1-1.5 0v-9.5a.75.75 0 0 1 .75-.75Z"/></svg>
                    </button>
                    <button type="button" data-viewer-action="autoplay" aria-pressed="false" class="btn-icon" title="Play through the game">
                        <span class="sr-only">Play through the game</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm6.39-2.908a.75.75 0 0 1 .766.027l3.5 2.25a.75.75 0 0 1 0 1.262l-3.5 2.25A.75.75 0 0 1 8 12.25v-4.5a.75.75 0 0 1 .39-.658Z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <button type="button" data-viewer-action="flip" class="btn-icon" title="Flip the board (F)">
                    <span class="sr-only">Flip the board</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.436-2.23a.75.75 0 0 0 .75-.75V4.202a.75.75 0 0 0-1.5 0v2.43l-.31-.31A7 7 0 0 0 3.976 9.46a.75.75 0 1 0 1.449.39 5.5 5.5 0 0 1 9.201-2.466l.312.311h-2.433a.75.75 0 0 0 0 1.5h4.243Z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <p data-viewer-status class="mt-2 text-xs font-medium text-stone-500" aria-live="polite">Starting position</p>

            <p data-viewer-error hidden class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 ring-1 ring-amber-200"></p>
        </div>

        {{-- Move list and annotation --}}
        <div class="border-t border-stone-200 bg-stone-50/70 lg:border-t-0 lg:border-l">
            <div class="border-b border-stone-200 px-4 py-2.5">
                <h2 class="text-xs font-semibold tracking-wider text-stone-500 uppercase">Moves</h2>
            </div>

            <div data-viewer-moves class="move-list max-h-72 overflow-y-auto lg:max-h-[30rem]"></div>

            <p data-viewer-comment hidden class="border-t border-stone-200 px-4 py-3 text-sm leading-relaxed text-stone-700 italic"></p>

            <div class="border-t border-stone-200 px-4 py-3">
                <label class="mb-1.5 block text-xs font-semibold tracking-wider text-stone-500 uppercase" for="viewer-fen-{{ $attributes->get('id', 'main') }}">
                    Position (FEN)
                </label>
                <div class="flex gap-1.5">
                    <input
                        id="viewer-fen-{{ $attributes->get('id', 'main') }}"
                        data-viewer-fen
                        type="text"
                        readonly
                        value="{{ \App\Support\ChessNotation::START_FEN }}"
                        class="w-full rounded-lg bg-white px-2.5 py-1.5 font-mono text-xs text-stone-600 ring-1 ring-stone-300 ring-inset"
                    >
                </div>
            </div>
        </div>
    </div>

    {{-- Fallback: the raw move text, always present in the HTML --}}
    @if ($moveText)
        <details class="border-t border-stone-200 px-4 py-3 text-sm">
            <summary class="cursor-pointer font-medium text-stone-600 hover:text-stone-900">
                Full notation
            </summary>
            <p class="mt-2 leading-relaxed break-words text-stone-600">{{ $moveText }}</p>
        </details>
    @endif
</div>
