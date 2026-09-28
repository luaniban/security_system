{{-- -/+ control. `decrement`/`increment` are Livewire actions; the value is always re-validated server side. --}}
@props(['value', 'min' => 1, 'max', 'decrement', 'increment', 'label' => 'Quantidade', 'size' => 'md'])

@php($box = $size === 'sm' ? 'h-9 w-9' : 'h-11 w-11')

<div {{ $attributes->merge(['class' => 'inline-flex items-center rounded-lg border border-gray-300 bg-white shadow-sm']) }} role="group" aria-label="{{ $label }}">
    <button type="button" wire:click="{{ $decrement }}" @disabled($value <= $min) class="{{ $box }} flex items-center justify-center rounded-s-lg text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-300" aria-label="Diminuir quantidade">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M5 12h14" /></svg>
    </button>
    <span class="min-w-10 px-1 text-center text-sm font-bold tabular-nums text-gray-900" aria-live="polite">{{ $value }}</span>
    <button type="button" wire:click="{{ $increment }}" @disabled($value >= $max) class="{{ $box }} flex items-center justify-center rounded-e-lg text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-300" aria-label="Aumentar quantidade">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14m-7-7h14" /></svg>
    </button>
</div>
