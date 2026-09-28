@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-brand-600 px-1 text-sm font-semibold text-gray-900 focus:outline-none focus-visible:bg-brand-50'
            : 'inline-flex items-center border-b-2 border-transparent px-1 text-sm font-medium text-gray-600 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus-visible:bg-brand-50 transition duration-150 ease-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
