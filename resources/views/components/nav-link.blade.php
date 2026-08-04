@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        'bg-club-800 text-white' => $active,
        'text-club-200 hover:bg-club-800/70 hover:text-white' => ! $active,
    ]) }}
>{{ $slot }}</a>
