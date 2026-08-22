@extends('layouts.members')

@section('members')
    @php
        $requestedKey = request('template');
        $activeKey = $templates->has($requestedKey) ? $requestedKey : $templates->keys()->first();
        $active = $templates->get($activeKey);
        $template = $active['template'];
        $definition = $active['definition'];
        $formAction = route('members.emails.handle', ['template' => $activeKey, 'view' => $activeKey]);
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold tracking-wider text-club-700 uppercase">Administration</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900">Automated emails</h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-stone-600">
                Edit the messages sent after a public enquiry or a successful member registration.
                Preview and test-send changes before saving them.
            </p>
        </div>
    </div>

    <div class="mt-7 flex flex-wrap gap-2 border-b border-stone-200" role="tablist" aria-label="Email templates">
        @foreach ($templates as $key => $entry)
            <a href="{{ route('members.emails.edit', ['template' => $key]) }}"
               class="-mb-px border-b-2 px-4 py-3 text-sm font-semibold {{ $activeKey === $key ? 'border-club-700 text-club-800' : 'border-transparent text-stone-500 hover:border-stone-300 hover:text-stone-800' }}"
               @if ($activeKey === $key) aria-current="page" @endif>
                {{ $entry['definition']['label'] }}
            </a>
        @endforeach
    </div>

    <form method="POST" action="{{ $formAction }}" class="mt-6">
        @csrf

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_19rem]">
            <div class="card p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-stone-900">{{ $definition['label'] }}</h2>
                        <p class="mt-1 text-sm text-stone-600">{{ $definition['description'] }}</p>
                    </div>

                    <label class="inline-flex shrink-0 cursor-pointer items-center gap-3 rounded-lg bg-stone-50 px-3 py-2 text-sm font-medium text-stone-800 ring-1 ring-stone-200">
                        <input type="checkbox" name="is_enabled" value="1"
                               @checked(old('is_enabled', $template->is_enabled))
                               class="h-4 w-4 rounded border-stone-300 text-club-700 focus:ring-club-600">
                        Send automatically
                    </label>
                </div>

                <div class="mt-6 space-y-6">
                    <div>
                        <label for="subject" class="field-label">Subject <span class="text-red-600">*</span></label>
                        <input id="subject" name="subject" type="text" required maxlength="190"
                               value="{{ old('subject', $template->subject) }}" class="field-input">
                        @error('subject') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="body" class="field-label">Message <span class="text-red-600">*</span></label>
                        <textarea id="body" name="body" rows="20" required maxlength="10000"
                                  class="field-input font-mono text-sm leading-relaxed">{{ old('body', $template->body) }}</textarea>
                        <p class="field-hint">
                            Plain text and Markdown are supported. Raw HTML is escaped for safety.
                        </p>
                        @error('body') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="signature" class="field-label">Signatory <span class="text-stone-400">(optional)</span></label>
                            <input id="signature" name="signature" type="text" maxlength="120"
                                   value="{{ old('signature', $template->signature) }}" class="field-input">
                            @error('signature') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="signature_role" class="field-label">Role <span class="text-stone-400">(optional)</span></label>
                            <input id="signature_role" name="signature_role" type="text" maxlength="120"
                                   value="{{ old('signature_role', $template->signature_role) }}" class="field-input">
                            @error('signature_role') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @error('action') <p class="field-error">{{ $message }}</p> @enderror
                    @error('test_email') <p class="field-error">{{ $message }}</p> @enderror

                    <div class="flex flex-wrap items-center gap-3 border-t border-stone-200 pt-5">
                        <button type="submit" name="action" value="save" class="btn-primary">
                            Save {{ strtolower($definition['label']) }}
                        </button>
                        <button type="submit" name="action" value="preview" formtarget="_blank" class="btn-secondary">
                            Preview unsaved changes
                        </button>
                        <button type="submit" name="action" value="test" class="btn-secondary"
                                onclick="return confirm('Send this unsaved version only to {{ auth()->user()->email }}?')">
                            Send test to me
                        </button>
                        <button type="submit" name="action" value="reset" formnovalidate
                                class="btn-secondary border-amber-300 bg-amber-50 text-amber-950 hover:bg-amber-100"
                                onclick="return confirm('Restore the approved default wording for this email?')">
                            Restore default
                        </button>
                    </div>
                </div>
            </div>

            <aside class="space-y-5">
                <div class="card p-5">
                    <h2 class="font-semibold text-stone-900">Available placeholders</h2>
                    <p class="mt-1 text-sm leading-relaxed text-stone-600">
                        These values are filled in automatically for each recipient. Copy them exactly, including the braces.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($definition['placeholders'] as $placeholder)
                            <code class="rounded-md bg-stone-100 px-2 py-1 text-xs text-stone-800">&#123;&#123;{{ $placeholder }}&#125;&#125;</code>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl bg-club-50 p-5 text-sm text-club-900 ring-1 ring-club-100">
                    <p class="font-semibold">Safe editing workflow</p>
                    <p class="mt-2 leading-relaxed text-club-800">
                        Preview first, then send a test to your own address. Saving affects only future emails and never resends an old message.
                    </p>
                </div>

                <div class="rounded-xl bg-amber-50 p-5 text-sm text-amber-950 ring-1 ring-amber-200">
                    <p class="font-semibold">About test messages</p>
                    <p class="mt-2 leading-relaxed text-amber-900">
                        Tests use fictional sample details and are labelled clearly. They are sent only to the signed-in administrator.
                    </p>
                </div>
            </aside>
        </div>
    </form>
@endsection
