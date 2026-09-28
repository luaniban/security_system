@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500']) }}>
    {{ $slot }}
</select>
