@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.$status->badgeClasses()]) }}>
    {{ $status->label() }}
</span>
