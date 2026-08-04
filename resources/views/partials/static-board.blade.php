@props([
    'fen',
    'orientation' => 'white',
    'coordinates' => true,
    'lastMove' => null,
])

{{--
    A read-only lichess board. The FEN is rendered into the markup, so the
    position is present even before JavaScript runs, and the CSS placeholder
    keeps the square reserved so the page never jumps.
--}}
<div
    class="board-frame"
    data-chess-board
    data-board-pending="true"
    data-fen="{{ $fen }}"
    data-orientation="{{ $orientation === 'black' ? 'black' : 'white' }}"
    data-coordinates="{{ $coordinates ? 'true' : 'false' }}"
    @if ($lastMove) data-last-move="{{ $lastMove }}" @endif
    role="img"
    aria-label="Chess position: {{ $fen }}"
></div>
