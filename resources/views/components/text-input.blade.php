@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-brand-500 focus:ring-brand-500 disabled:bg-gray-100 disabled:text-gray-500']) }}>
