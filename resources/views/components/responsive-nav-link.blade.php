@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex min-h-11 w-full items-center border-l-4 border-brand-600 bg-brand-50 ps-3 pe-4 text-start text-base font-semibold text-brand-800 focus:outline-none'
            : 'flex min-h-11 w-full items-center border-l-4 border-transparent ps-3 pe-4 text-start text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus-visible:bg-gray-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
