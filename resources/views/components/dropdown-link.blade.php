@props(['href'])

<a
    href="{{ $href }}"
    {{ $attributes->class('flex items-center gap-2 px-4 py-2 text-sm text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900') }}
>{{ $slot }}</a>
