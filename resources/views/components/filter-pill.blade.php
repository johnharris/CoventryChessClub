@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if ($active) aria-current="true" @endif
    {{ $attributes->class([
        'rounded-full px-4 py-2 text-sm font-semibold transition-colors',
        'bg-club-700 text-white' => $active,
        'bg-white text-stone-700 ring-1 ring-stone-300 ring-inset hover:bg-stone-50' => ! $active,
    ]) }}
>{{ $slot }}</a>
