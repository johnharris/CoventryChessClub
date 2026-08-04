@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'block rounded-lg px-3 py-2.5 text-base font-medium transition-colors',
        'bg-club-800 text-white' => $active,
        'text-club-100 hover:bg-club-800' => ! $active,
    ]) }}
>{{ $slot }}</a>
