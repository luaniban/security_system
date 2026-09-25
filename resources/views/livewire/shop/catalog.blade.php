@use('App\Support\Money')
@use('App\Services\ShippingCalculator')

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4 flex flex-col gap-10">
    @if ($search === '' && $products->onFirstPage())
        <section class="rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 px-6 py-10 text-white sm:px-12 sm:py-14">
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-300">Marketplace {{ config('app.name') }}</p>
            <h1 class="mt-3 max-w-2xl text-3xl font-bold leading-tight sm:text-5xl">Compre de vendedores independentes com pagamento protegido</h1>
            <p class="mt-4 max-w-xl text-base text-indigo-100 sm:text-lg">Encontre produtos de todo o Brasil e receba cada pacote direto de quem vende.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#produtos" class="inline-flex items-center rounded-lg bg-white px-5 py-3 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">Ver produtos</a>
                <a href="{{ auth()->check() ? route('seller.products.create') : route('register') }}" class="inline-flex items-center rounded-lg px-5 py-3 text-sm font-semibold text-white ring-1 ring-inset ring-white/40 hover:bg-white/10">Comece a vender</a>
            </div>
        </section>

        <ul class="grid gap-4 sm:grid-cols-3" aria-label="Vantagens">
            <li class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4">
                <svg class="h-6 w-6 shrink-0 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                <div>
                    <p class="font-semibold">Frete grátis</p>
                    <p class="text-sm text-gray-600">Acima de {{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }} por vendedor</p>
                </div>
            </li>
            <li class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4">
                <svg class="h-6 w-6 shrink-0 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                <div>
                    <p class="font-semibold">Pagamento seguro</p>
                    <p class="text-sm text-gray-600">O CVV do seu cartão nunca é armazenado</p>
                </div>
            </li>
            <li class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4">
                <svg class="h-6 w-6 shrink-0 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                <div>
                    <p class="font-semibold">Pedido rastreável</p>
                    <p class="text-sm text-gray-600">Código de rastreio para cada pacote</p>
                </div>
            </li>
        </ul>
    @endif

    <section id="produtos" class="scroll-mt-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                @if ($search !== '')
                    <h1 class="text-2xl font-bold">Resultados para “{{ mb_substr($search, 0, 100) }}”</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $products->total() }} {{ $products->total() === 1 ? 'produto encontrado' : 'produtos encontrados' }} ·
                        <a href="{{ route('home') }}" class="font-medium text-indigo-700 underline hover:text-indigo-900">Limpar busca</a>
                    </p>
                @else
                    <h2 class="text-2xl font-bold">Produtos em destaque</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $products->total() }} {{ $products->total() === 1 ? 'produto disponível' : 'produtos disponíveis' }}</p>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <label for="sort" class="text-sm text-gray-600 whitespace-nowrap">Ordenar por</label>
                <x-select-input wire:model.live="sort" id="sort" class="text-sm">
                    <option value="recentes">Mais recentes</option>
                    <option value="menor-preco">Menor preço</option>
                    <option value="maior-preco">Maior preço</option>
                    <option value="nome">Nome</option>
                </x-select-input>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4" wire:loading.class="opacity-60">
            @forelse ($products as $product)
                <a wire:key="product-{{ $product->id }}" href="{{ route('products.show', $product) }}"
                   class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:border-gray-300 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                    <div class="relative aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="" class="h-full w-full object-cover transition duration-300 motion-safe:group-hover:scale-105" loading="lazy">
                        @else
                            <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                        @endif

                        <div class="absolute left-2 top-2 flex flex-col items-start gap-1">
                            @if ($product->price_cents >= ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS)
                                <span class="rounded-md bg-green-600 px-2 py-0.5 text-xs font-semibold text-white">Frete grátis</span>
                            @endif
                            @if ($product->stock <= 5)
                                <span class="rounded-md bg-amber-400 px-2 py-0.5 text-xs font-semibold text-gray-900">Últimas unidades</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col gap-1 p-3 sm:p-4">
                        <h3 class="text-sm font-medium text-gray-900 line-clamp-2 group-hover:text-indigo-700">{{ $product->name }}</h3>
                        <p class="mt-auto pt-2 text-lg font-bold text-gray-900 sm:text-xl">{{ Money::format($product->price_cents) }}</p>
                        <p class="text-xs text-gray-500 truncate">Vendido por {{ $product->seller->name }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full flex flex-col items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                    <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <p class="text-lg font-semibold">Nenhum produto encontrado</p>
                    <p class="text-sm text-gray-600">Tente buscar por outro termo ou veja todos os produtos disponíveis.</p>
                    @if ($search !== '')
                        <a href="{{ route('home') }}" class="mt-2 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Ver todos os produtos</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    </section>
</div>
