@php
    /** @var \App\Models\Post|null $post */
    $post = $post ?? null;
    $currentType = old('type', $post?->type ?? ($type ?? \App\Models\Post::TYPE_GENERAL));
    $orientation = old('orientation', $post?->orientation ?? 'white');
    $user = auth()->user();
@endphp

{{-- ---------- Post type ---------- --}}
<fieldset class="card p-5 sm:p-6">
    <legend class="text-sm font-semibold text-stone-900">What kind of post is this?</legend>
    <p class="mt-1 text-sm text-stone-600">
        This decides which fields you get. You can change it at any time.
    </p>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        @foreach ([
            \App\Models\Post::TYPE_GENERAL => ['General post', 'News, fixtures, results or an article.'],
            \App\Models\Post::TYPE_POSITION => ['Chess position', 'One diagram, built from a FEN.'],
            \App\Models\Post::TYPE_GAME => ['Annotated game', 'A full game from a PGN.'],
        ] as $value => [$label, $help])
            <label
                data-post-type-option="{{ $value }}"
                class="relative flex cursor-pointer flex-col rounded-lg border p-4 transition-colors data-[active=true]:border-club-600 data-[active=true]:bg-club-50 data-[active=false]:border-stone-300 data-[active=false]:hover:bg-stone-50"
            >
                <input
                    type="radio"
                    name="type"
                    value="{{ $value }}"
                    data-post-type-input
                    @checked($currentType === $value)
                    class="absolute top-4 right-4 h-4 w-4 border-stone-300 text-club-700 focus:ring-club-600"
                >
                <span class="pr-6 font-semibold text-stone-900">{{ $label }}</span>
                <span class="mt-1 text-sm text-stone-600">{{ $help }}</span>
            </label>
        @endforeach
    </div>
</fieldset>

{{-- ---------- Basics ---------- --}}
<div class="card mt-5 p-5 sm:p-6">
    <div class="space-y-5">
        <div>
            <label for="title" class="field-label">Title <span class="text-red-600">*</span></label>
            <input id="title" name="title" type="text" required maxlength="180"
                   value="{{ old('title', $post?->title ?? '') }}" class="field-input">
            @error('title') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="excerpt" class="field-label">
                Summary <span class="text-stone-400">(optional)</span>
            </label>
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="400" class="field-input">{{ old('excerpt', $post?->excerpt ?? '') }}</textarea>
            <p class="field-hint">Shown on the blog index and in link previews. Left blank, we use the opening of your post.</p>
            @error('excerpt') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

{{-- ---------- Position fields ---------- --}}
<div data-post-type-panel="{{ \App\Models\Post::TYPE_POSITION }}" hidden class="mt-5">
    <div class="card p-5 sm:p-6" data-fen-editor>
        <h2 class="text-sm font-semibold text-stone-900">The position</h2>
        <p class="mt-1 text-sm text-stone-600">
            Paste a FEN, or set the position up by dragging pieces on the board — the FEN keeps
            itself in step. Dragging a piece off the board removes it.
        </p>

        <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)]">
            <div>
                <div data-fen-board class="board-frame" data-board-pending="true"></div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" data-fen-preset="start" class="btn-secondary !px-3 !py-1.5 text-xs">Starting position</button>
                    <button type="button" data-fen-preset="empty" class="btn-secondary !px-3 !py-1.5 text-xs">Empty board</button>
                    <button type="button" data-fen-flip class="btn-secondary !px-3 !py-1.5 text-xs">Flip view</button>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="fen" class="field-label">FEN</label>
                    <textarea id="fen" name="fen" rows="3" data-fen-input
                              class="field-input font-mono text-sm">{{ old('fen', $post?->fen ?? \App\Support\ChessNotation::START_FEN) }}</textarea>
                    <p data-fen-feedback data-state="neutral"
                       class="mt-1.5 text-sm data-[state=error]:font-medium data-[state=error]:text-red-600 data-[state=neutral]:text-stone-500 data-[state=ok]:text-club-700"></p>
                    @error('fen') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <span class="field-label">Side to move</span>
                        <div class="flex gap-2">
                            @foreach (['w' => 'White', 'b' => 'Black'] as $value => $label)
                                <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">
                                    <input type="radio" data-fen-turn value="{{ $value }}"
                                           @checked(\App\Support\ChessNotation::sideToMove(old('fen', $post?->fen ?? \App\Support\ChessNotation::START_FEN)) === $value)
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
                                    <input type="radio" name="orientation" value="{{ $value }}" data-orientation-input
                                           @checked($orientation === $value)
                                           class="h-4 w-4 border-stone-300 text-club-700 focus:ring-club-600">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label for="caption" class="field-label">Caption <span class="text-stone-400">(optional)</span></label>
                    <input id="caption" name="caption" type="text" maxlength="180"
                           placeholder="White to play and win"
                           value="{{ old('caption', $post?->caption ?? '') }}" class="field-input">
                    @error('caption') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="solution" class="field-label">Answer <span class="text-stone-400">(optional)</span></label>
                    <input id="solution" name="solution" type="text" maxlength="255"
                           placeholder="1. Qh6+! gxh6 2. Rg8#"
                           value="{{ old('solution', $post?->solution ?? '') }}" class="field-input">
                    <p class="field-hint">Hidden behind a “Show the answer” toggle, so readers can try it first.</p>
                    @error('solution') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ---------- Game fields ---------- --}}
<div data-post-type-panel="{{ \App\Models\Post::TYPE_GAME }}" hidden class="mt-5">
    <div class="card p-5 sm:p-6" data-pgn-editor>
        <h2 class="text-sm font-semibold text-stone-900">The game</h2>
        <p class="mt-1 text-sm text-stone-600">
            Paste the PGN, or drop a <code class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-xs">.pgn</code>
            file onto the box. Comments in <code class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-xs">{curly braces}</code>
            become the annotations readers see beside each move.
        </p>

        <div class="mt-5 space-y-5">
            <div data-pgn-drop class="rounded-lg border-2 border-dashed border-stone-300 p-3 transition-colors data-[dragging=true]:border-club-500 data-[dragging=true]:bg-club-50">
                <label for="pgn" class="field-label">PGN</label>
                <textarea id="pgn" name="pgn" rows="10" data-pgn-input
                          placeholder='[Event "Coventry League Division 1"]&#10;[White "Smith, J"]&#10;[Black "Jones, A"]&#10;[Result "1-0"]&#10;&#10;1. e4 e5 2. Nf3 Nc6 3. Bb5 {The Spanish. } a6 ...'
                          class="field-input font-mono text-sm">{{ old('pgn', $post?->pgn ?? '') }}</textarea>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <label class="btn-secondary cursor-pointer !py-1.5 text-xs">
                        Choose a .pgn file
                        <input type="file" data-pgn-file accept=".pgn,.txt" class="sr-only">
                    </label>
                    <p data-pgn-feedback data-state="neutral"
                       class="text-sm data-[state=error]:font-medium data-[state=error]:text-red-600 data-[state=neutral]:text-stone-500 data-[state=ok]:text-club-700"></p>
                </div>
            </div>
            @error('pgn') <p class="field-error">{{ $message }}</p> @enderror

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="white_player" class="field-label">White</label>
                    <input id="white_player" name="white_player" type="text" maxlength="120"
                           value="{{ old('white_player', $post?->white_player ?? '') }}" class="field-input">
                </div>
                <div>
                    <label for="black_player" class="field-label">Black</label>
                    <input id="black_player" name="black_player" type="text" maxlength="120"
                           value="{{ old('black_player', $post?->black_player ?? '') }}" class="field-input">
                </div>
                <div>
                    <label for="result" class="field-label">Result</label>
                    <select id="result" name="result" class="field-input">
                        <option value="">Not stated</option>
                        @foreach (['1-0' => 'White won (1-0)', '0-1' => 'Black won (0-1)', '1/2-1/2' => 'Draw (½-½)', '*' => 'Unfinished (*)'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('result', $post?->result ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="played_on" class="field-label">Date played</label>
                    <input id="played_on" name="played_on" type="date"
                           value="{{ old('played_on', $post?->played_on?->toDateString() ?? '') }}" class="field-input">
                </div>
                <div class="sm:col-span-2">
                    <label for="event" class="field-label">Event or competition</label>
                    <input id="event" name="event" type="text" maxlength="180"
                           placeholder="Coventry &amp; District League, Division 1"
                           value="{{ old('event', $post?->event ?? '') }}" class="field-input">
                </div>
                <div class="sm:col-span-2">
                    <span class="field-label">Show the board from</span>
                    <div class="flex gap-2 sm:max-w-sm">
                        @foreach (['white' => "White's side", 'black' => "Black's side"] as $value => $label)
                            <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">
                                <input type="radio" name="orientation" value="{{ $value }}"
                                       @checked($orientation === $value)
                                       class="h-4 w-4 border-stone-300 text-club-700 focus:ring-club-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Live preview, using the same viewer readers get --}}
            <div>
                <h3 class="field-label">Preview</h3>
                <div data-pgn-preview></div>
            </div>
        </div>
    </div>
</div>

{{-- ---------- Body ---------- --}}
<div class="card mt-5 p-5 sm:p-6">
    <label for="body" class="field-label">
        {{ $currentType === \App\Models\Post::TYPE_GENERAL ? 'Post' : 'Notes and commentary' }}
        @if ($currentType !== \App\Models\Post::TYPE_GENERAL)
            <span class="text-stone-400">(optional)</span>
        @endif
    </label>
    <textarea id="body" name="body" rows="16" class="field-input font-mono text-sm">{{ old('body', $post?->body ?? '') }}</textarea>

    <div class="mt-2.5 rounded-lg bg-stone-50 p-3.5 text-sm text-stone-600 ring-1 ring-stone-200">
        <p class="font-medium text-stone-800">Formatting</p>
        <p class="mt-1.5">
            Markdown: <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">## Heading</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">**bold**</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">*italic*</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">- lists</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">[link](https://…)</code>, and tables.
        </p>
        <p class="mt-2">
            To drop a board into the text, use a fenced block starting with
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">```fen</code>, the FEN on the
            next line, then optional <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Caption:</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Orientation:</code> and
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Solution:</code> lines.
        </p>
    </div>
    @error('body') <p class="field-error">{{ $message }}</p> @enderror
</div>

{{-- ---------- Publishing ---------- --}}
<div class="card mt-5 p-5 sm:p-6">
    <h2 class="text-sm font-semibold text-stone-900">Publishing</h2>

    <div class="mt-4 space-y-4">
        <label class="flex items-start gap-3">
            <input type="checkbox" name="is_published" value="1"
                   @checked(old('is_published', $post?->is_published ?? false))
                   class="mt-0.5 h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
            <span class="text-sm">
                <span class="font-medium text-stone-900">Publish this post</span>
                <span class="block text-stone-600">Unticked, it stays a draft that only you and the administrators can see.</span>
            </span>
        </label>

        @if ($user->isAdmin())
            <label class="flex items-start gap-3">
                <input type="checkbox" name="is_featured" value="1"
                       @checked(old('is_featured', $post?->is_featured ?? false))
                       class="mt-0.5 h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
                <span class="text-sm">
                    <span class="font-medium text-stone-900">Feature on the home page</span>
                    <span class="block text-stone-600">Pins this post to the top of the home page.</span>
                </span>
            </label>
        @endif

        <div class="sm:max-w-xs">
            <label for="published_at" class="field-label">
                Publication date <span class="text-stone-400">(optional)</span>
            </label>
            <input id="published_at" name="published_at" type="datetime-local"
                   value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i') ?? '') }}"
                   class="field-input">
            <p class="field-hint">Leave blank to use the moment you publish. A future date schedules it.</p>
        </div>
    </div>
</div>

{{-- The PGN preview reuses the real viewer markup --}}
<template data-pgn-preview-template>
    @include('partials.game-viewer', ['pgn' => '', 'orientation' => 'white', 'moveText' => null])
</template>
