@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold '.$status->badgeClasses()]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
