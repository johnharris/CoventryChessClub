@php $page = $page ?? null; @endphp

<div class="card p-5 sm:p-6">
    <div class="space-y-5">
        <div>
            <label for="title" class="field-label">Page title <span class="text-red-600">*</span></label>
            <input id="title" name="title" type="text" required maxlength="180"
                   value="{{ old('title', $page?->title ?? '') }}" class="field-input">
            @if ($page)
                <p class="field-hint">Web address: <code class="font-mono text-xs">/{{ $page->slug }}</code></p>
            @endif
        </div>

        <div>
            <label for="body" class="field-label">Page content</label>
            <textarea id="body" name="body" rows="20" class="field-input font-mono text-sm">{{ old('body', $page?->body ?? '') }}</textarea>
            <p class="field-hint">
                Markdown, exactly as in posts — headings, lists, links, tables, and
                <code class="font-mono text-xs">```fen</code> blocks for diagrams.
            </p>
            @error('body') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="card mt-5 p-5 sm:p-6">
    <h2 class="text-sm font-semibold text-stone-900">Visibility</h2>

    <div class="mt-4 space-y-4">
        <label class="flex items-start gap-3">
            <input type="checkbox" name="is_published" value="1"
                   @checked(old('is_published', $page?->is_published ?? true))
                   class="mt-0.5 h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
            <span class="text-sm">
                <span class="font-medium text-stone-900">Publish this page</span>
                <span class="block text-stone-600">Unticked, the page is hidden from visitors.</span>
            </span>
        </label>

        <label class="flex items-start gap-3">
            <input type="checkbox" name="show_in_nav" value="1"
                   @checked(old('show_in_nav', $page?->show_in_nav ?? true))
                   class="mt-0.5 h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
            <span class="text-sm">
                <span class="font-medium text-stone-900">Show in the site menu</span>
                <span class="block text-stone-600">Adds the page to the main navigation.</span>
            </span>
        </label>

        <div class="sm:max-w-32">
            <label for="nav_order" class="field-label">Menu position</label>
            <input id="nav_order" name="nav_order" type="number" min="0" max="999"
                   value="{{ old('nav_order', $page?->nav_order ?? 0) }}" class="field-input">
            <p class="field-hint">Lower first.</p>
        </div>
    </div>
</div>
