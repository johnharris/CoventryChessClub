@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'shrink-0 border-b-2 px-1 py-2.5 text-sm font-semibold whitespace-nowrap transition-colors',
        'border-club-700 text-club-800' => $active,
        'border-transparent text-stone-500 hover:border-stone-300 hover:text-stone-800' => ! $active,
    ]) }}
>{{ $slot }}</a>
