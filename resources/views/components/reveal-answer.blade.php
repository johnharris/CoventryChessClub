{{--
    A puzzle answer that stays hidden until the reader asks for it.

    Built on <details>/<summary> rather than a JavaScript panel for one reason:
    it works when JavaScript does not. A reader on a locked-down work computer,
    or an old phone, still gets a working button. The styling turns what browsers
    draw as a small triangle and a line of text into something that plainly looks
    like a button.

    @param string $solution   The answer text.
    @param string $label      Optional button wording.
--}}

@props(['solution' => null, 'label' => 'Show the answer'])

@if (filled($solution))
    <details {{ $attributes->merge(['class' => 'reveal-answer mt-4']) }}>
        <summary class="reveal-answer__button">
            <svg class="reveal-answer__icon h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
            </svg>
            <span class="reveal-answer__show">{{ $label }}</span>
            <span class="reveal-answer__hide">Hide the answer</span>
        </summary>

        <div class="reveal-answer__body">
            {{ $solution }}
        </div>
    </details>
@endif
