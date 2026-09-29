@use('App\Support\Money')
@use('App\Services\ShippingCalculator')

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4 flex flex-col gap-10">
    @if ($search === '')
        <section class="overflow-hidden rounded-3xl bg-brand-900 text-white">
            <div class="grid gap-8 px-5 py-7 sm:px-10 sm:py-12 lg:grid-cols-[1.4fr_1fr] lg:items-center">
                <div>
                    <h1 class="max-w-xl text-3xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">Compre de quem vende, com o pagamento protegido.</h1>
                    <p class="mt-4 hidden max-w-lg text-base text-brand-100 sm:block sm:text-lg">Produtos de vendedores de todo o Brasil. Cada pacote sai direto do vendedor, com código de rastreio.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#produtos" class="hidden sm:inline-flex min-h-11 items-center rounded-lg bg-white px-5 text-sm font-bold text-brand-800 shadow-sm hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-brand-900">Ver produtos</a>
                        <a href="{{ auth()->check() ? route('seller.products.create') : route('register') }}" class="inline-flex min-h-11 items-center rounded-lg px-5 text-sm font-bold text-white ring-1 ring-inset ring-white/40 hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Anunciar um produto</a>
                    </div>
                </div>

                <ul class="hidden gap-3 text-sm sm:grid sm:grid-cols-3 lg:grid-cols-1" aria-label="Por que comprar aqui">
                    <li class="flex items-start gap-3 rounded-xl bg-white/5 p-3 ring-1 ring-inset ring-white/10">
                        <svg class="h-5 w-5 shrink-0 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        <span><strong class="font-bold text-white">Pagamento seguro.</strong> <span class="text-brand-100">O CVV do cartão nunca é guardado.</span></span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-white/5 p-3 ring-1 ring-inset ring-white/10">
                        <svg class="h-5 w-5 shrink-0 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                        <span><strong class="font-bold text-white">Frete grátis</strong> <span class="text-brand-100">acima de {{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }} por vendedor.</span></span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-white/5 p-3 ring-1 ring-inset ring-white/10">
                        <svg class="h-5 w-5 shrink-0 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                        <span><strong class="font-bold text-white">Entrega rastreada.</strong> <span class="text-brand-100">Acompanhe cada pacote pelo pedido.</span></span>
                    </li>
                </ul>
            </div>
        </section>
    @endif

    <section id="produtos" class="scroll-mt-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                @if ($search !== '')
                    <h1 class="text-2xl font-extrabold tracking-tight">Resultados para “{{ mb_substr($search, 0, 100) }}”</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $total }} {{ $total === 1 ? 'produto encontrado' : 'produtos encontrados' }}.
                        <a href="{{ route('home') }}" class="font-medium text-brand-700 underline hover:text-brand-900">Limpar busca</a>
                    </p>
                @else
                    <h2 class="text-2xl font-extrabold tracking-tight">Produtos à venda</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        @if ($products->count() < $total)
                            Mostrando {{ $products->count() }} de {{ $total }} produtos
                        @else
                            {{ $total }} {{ $total === 1 ? 'produto disponível' : 'produtos disponíveis' }}
                        @endif
                    </p>
                @endif
            </div>

            @php($sortOptions = ['recentes' => 'Mais recentes', 'menor-preco' => 'Menor preço', 'maior-preco' => 'Maior preço', 'nome' => 'A–Z'])
            <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" role="group" aria-label="Ordenar produtos">
                <div class="inline-flex min-w-max gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-card">
                    @foreach ($sortOptions as $value => $label)
                        <button type="button" wire:key="sort-{{ $value }}" wire:click="sortBy('{{ $value }}')" aria-pressed="{{ $sort === $value ? 'true' : 'false' }}"
                                class="min-h-9 rounded-lg px-3 text-sm font-semibold transition duration-150 ease-out {{ $sort === $value ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Dimmed only while re-sorting: a scroll load appends to the grid and must not flash it. --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4" wire:loading.class="opacity-60" wire:target="sortBy">
            @forelse ($products as $product)
                <a wire:key="product-{{ $product->id }}" href="{{ route('products.show', $product) }}"
                   class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-card transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lift focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                    <div class="relative aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="" class="h-full w-full object-cover transition duration-300 motion-safe:group-hover:scale-105" loading="lazy">
                        @else
                            <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                        @endif

                        <div class="absolute left-2 top-2 flex flex-col items-start gap-1">
                            @if ($product->price_cents >= ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS)
                                <span class="rounded-md bg-brand-600 px-2 py-0.5 text-xs font-bold text-white shadow-sm">Frete grátis</span>
                            @endif
                            @if ($product->stock <= 5)
                                <span class="rounded-md bg-amber-300 px-2 py-0.5 text-xs font-bold text-gray-900 shadow-sm">Últimas unidades</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col gap-1 p-3 sm:p-4">
                        <h3 class="text-sm font-semibold leading-snug text-gray-900 line-clamp-2 group-hover:text-brand-700">{{ $product->name }}</h3>
                        <p class="mt-auto pt-2 text-lg font-extrabold tabular-nums tracking-tight text-gray-900 sm:text-xl">{{ Money::format($product->price_cents) }}</p>
                        <p class="text-xs text-gray-500 truncate">Vendido por {{ $product->seller->name }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full flex flex-col items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                    <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <p class="text-lg font-semibold">Nenhum produto encontrado</p>
                    <p class="text-sm text-gray-600">Tente buscar por outro termo ou veja todos os produtos disponíveis.</p>
                    @if ($search !== '')
                        <a href="{{ route('home') }}" class="mt-2 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Ver todos os produtos</a>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($hasMore)
            {{-- Sentinel: entering the viewport (200px early) loads the next batch. The
                 button is the fallback for keyboard users and for the case where the
                 sentinel stays on screen after a load, so the observer does not re-fire. --}}
            <div wire:key="catalog-load-more" wire:intersect.margin.200px="loadMore" class="mt-10 flex flex-col items-center gap-2">
                <div wire:loading.flex wire:target="loadMore" role="status" class="min-h-11 items-center gap-3 text-sm font-semibold text-gray-600">
                    <svg class="h-5 w-5 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />
                    </svg>
                    Carregando mais produtos…
                </div>

                <button type="button" wire:click="loadMore" wire:loading.remove wire:target="loadMore"
                        class="inline-flex min-h-11 items-center rounded-lg border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-700 shadow-card transition duration-150 ease-out hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                    Carregar mais produtos
                </button>
            </div>
        @elseif ($reachedLimit)
            <p class="mt-10 text-center text-sm text-gray-600">
                Mostrando os primeiros {{ $products->count() }} produtos. Use a busca para encontrar algo específico.
            </p>
        @elseif ($products->isNotEmpty())
            <p class="mt-10 text-center text-sm text-gray-500">Você chegou ao fim do catálogo.</p>
        @endif
    </section>
</div>
