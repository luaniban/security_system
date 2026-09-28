@use('App\Support\Money')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-seller-nav />

    <x-page-header title="Meus produtos" subtitle="Pause um anúncio quando não puder vender e reative quando quiser.">
        <a href="{{ route('seller.products.create') }}" class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-bold text-white shadow-sm hover:bg-brand-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14m-7-7h14" /></svg>Anunciar produto</a>
    </x-page-header>

    <ul class="space-y-3">
        @forelse ($products as $product)
            <li wire:key="product-{{ $product->id }}" class="flex flex-wrap items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-card sm:flex-nowrap">
                <a href="{{ route('products.show', $product) }}" class="flex min-w-0 flex-1 items-center gap-4">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate font-bold text-gray-900 hover:text-brand-700">{{ $product->name }}</span>
                        <span class="block text-sm tabular-nums text-gray-600">{{ Money::format($product->price_cents) }}, {{ $product->stock === 1 ? '1 em estoque' : $product->stock.' em estoque' }}</span>
                    </span>
                </a>

                <div class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-end">
                    <button type="button" wire:click="toggleActive({{ $product->id }})" role="switch" aria-checked="{{ $product->is_active ? 'true' : 'false' }}" aria-label="Anúncio de {{ $product->name }} ativo"
                            class="flex min-h-10 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        <span class="relative inline-flex h-6 w-10 shrink-0 rounded-full transition duration-150 ease-out {{ $product->is_active ? 'bg-brand-600' : 'bg-gray-300' }}" aria-hidden="true">
                            <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition duration-150 ease-out {{ $product->is_active ? 'translate-x-[18px]' : 'translate-x-0.5' }}"></span>
                        </span>
                        {{ $product->is_active ? 'Ativo' : 'Pausado' }}
                    </button>
                    <div class="flex gap-1">
                        <a href="{{ route('seller.products.edit', $product) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">Editar</a>
                        <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Excluir este anúncio? Esta ação não pode ser desfeita." class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-red-700 hover:bg-red-50">Excluir</button>
                    </div>
                </div>
            </li>
        @empty
            <li class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                <p class="text-lg font-bold">Você ainda não anunciou nenhum produto</p>
                <p class="text-sm text-gray-600">Crie seu primeiro anúncio em poucos minutos: nome, preço, estoque e uma foto.</p>
                <a href="{{ route('seller.products.create') }}" class="mt-2 inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-5 text-sm font-bold text-white hover:bg-brand-700">Anunciar produto</a>
            </li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $products->links() }}</div>
</div>
