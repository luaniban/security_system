@if (session('status'))
    <div x-data="{ show: true }" x-show="show" class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-900" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <p class="flex-1 font-medium">{{ session('status') }}</p>
            <button type="button" @click="show = false" class="-m-2 flex h-9 w-9 items-center justify-center rounded-lg text-brand-700 hover:bg-brand-100" aria-label="Fechar aviso">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>
@endif
