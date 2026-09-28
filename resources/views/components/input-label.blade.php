@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-gray-800']) }}>
    {{ $value ?? $slot }}
</label>
