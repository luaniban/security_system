@php
    // Let callers override the surface (e.g. a highlighted amber card) without fighting Tailwind's class order.
    $custom = (string) $attributes->get('class', '');
    $background = preg_match('/(^|\s)bg-/', $custom) ? '' : 'bg-white';
    $borderColor = preg_match('/(^|\s)border-(gray|brand|amber|red|green)-/', $custom) ? '' : 'border-gray-200';
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border {$borderColor} {$background} p-5 shadow-card sm:p-6"]) }}>
    {{ $slot }}
</div>
