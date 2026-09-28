<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-brand-700 active:bg-brand-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
