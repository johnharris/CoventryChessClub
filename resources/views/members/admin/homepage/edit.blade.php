@extends('layouts.members')

@section('members')
    @php
        $heroFen = old('hero_fen', $settings->hero_fen);
        $heroOrientation = old('hero_orientation', $settings->hero_orientation);
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold tracking-wider text-club-700 uppercase">Homepage</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900">Header chess position</h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-stone-600">
                Choose the position shown beside the welcome message. Paste a FEN or arrange the pieces
                directly on the board, then add a short caption explaining why the position matters.
            </p>
        </div>

        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn-secondary shrink-0">
            View homepage
        </a>
    </div>

    <form method="POST" action="{{ route('members.homepage.update') }}" class="mt-7">
        @csrf
        @method('PUT')

        <div class="card p-5 sm:p-6" data-fen-editor>
            <div class="grid gap-7 lg:grid-cols-[minmax(0,24rem)_minmax(0,1fr)]">
                <div>
                    <h2 class="text-sm font-semibold text-stone-900">Position preview</h2>
                    <p class="mt-1 text-sm text-stone-600">
                        Drag pieces to move them. Drag a piece off the board to remove it.
                    </p>

                    <div class="board-frame mt-4" data-fen-board data-board-pending="true"></div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" data-fen-preset="start" class="btn-secondary !px-3 !py-1.5 text-xs">
                            Starting position
                        </button>
                        <button type="button" data-fen-preset="empty" class="btn-secondary !px-3 !py-1.5 text-xs">
                            Empty board
                        </button>
                        <button type="button" data-fen-flip class="btn-secondary !px-3 !py-1.5 text-xs">
                            Flip preview
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="hero_fen" class="field-label">FEN <span class="text-red-600">*</span></label>
                        <textarea id="hero_fen" name="hero_fen" rows="3" required maxlength="255"
                                  data-fen-input class="field-input font-mono text-sm">{{ $heroFen }}</textarea>
                        <p data-fen-feedback data-state="neutral"
                           class="mt-1.5 text-sm data-[state=error]:font-medium data-[state=error]:text-red-600 data-[state=neutral]:text-stone-500 data-[state=ok]:text-club-700"></p>
                        @error('hero_fen') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <span class="field-label">Side to move</span>
                            <div class="flex gap-2">
                                @foreach (['w' => 'White', 'b' => 'Black'] as $value => $label)
                                    <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">
                                        <input type="radio" data-fen-turn value="{{ $value }}"
                                               @checked(\App\Support\ChessNotation::sideToMove($heroFen) === $value)
                                               class="h-4 w-4 border-stone-300 text-club-700 focus:ring-club-600">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <span class="field-label">Show the board from</span>
                            <div class="flex gap-2">
                                @foreach (['white' => "White's side", 'black' => "Black's side"] as $value => $label)
                                    <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">
                                        <input type="radio" name="hero_orientation" value="{{ $value }}" required
                                               data-orientation-input @checked($heroOrientation === $value)
                                               class="h-4 w-4 border-stone-300 text-club-700 focus:ring-club-600">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            @error('hero_orientation') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="hero_caption" class="field-label">
                            Caption <span class="text-stone-400">(optional)</span>
                        </label>
                        <input id="hero_caption" name="hero_caption" type="text" maxlength="180"
                               placeholder="Winning position from the Summer Cup final"
                               value="{{ old('hero_caption', $settings->hero_caption) }}" class="field-input">
                        <p class="field-hint">
                            This appears directly beneath the board. Include the event, players or result if useful.
                        </p>
                        @error('hero_caption') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-xl bg-club-50 p-4 text-sm text-club-900 ring-1 ring-club-100">
                        <p class="font-semibold">Suggested workflow for a tournament finish</p>
                        <p class="mt-1.5 leading-relaxed text-club-800">
                            Open the game in your chess software, move to the decisive final position, copy its FEN,
                            paste it above, choose the viewpoint, and describe the moment in the caption.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-stone-200 pt-5">
                        <button type="submit" class="btn-primary">Save homepage position</button>
                        <a href="{{ route('home') }}" class="btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card mt-6 border-amber-200 bg-amber-50 p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-amber-950">Restore the original position</h2>
                <p class="mt-1 text-sm text-amber-900">
                    This replaces the saved position and caption with the original Italian Game display.
                </p>
            </div>

            <form method="POST" action="{{ route('members.homepage.reset') }}" class="shrink-0"
                  onsubmit="return confirm('Restore the Italian Game position on the homepage?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary border-amber-300 bg-white text-amber-950 hover:bg-amber-100">
                    Restore Italian Game
                </button>
            </form>
        </div>
    </div>
@endsection
