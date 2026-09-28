@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-900']) }} role="status">
        {{ $status }}
    </div>
@endif
