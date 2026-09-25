@use('App\Support\Money')
@use('App\Services\ShippingCalculator')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <nav aria-label="Trilha de navegação" class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('home') }}" class="hover:text-indigo-700 hover:underline">Loja</a></li>
            <li aria-hidden="true">/</li>
            <li class="truncate text-gray-900" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="grid gap-8 md:grid-cols-2 lg:gap-12">
        <div class="aspect-square overflow-hidden rounded-2xl border border-gray-200 bg-white flex items-center justify-center">
            @if ($product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <svg class="h-20 w-20" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                    <span class="text-sm">Sem imagem</span>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-6">
            <div>
                <p class="text-sm text-gray-500">Vendido por <span class="font-medium text-gray-900">{{ $product->seller->name }}</span></p>
                <h1 class="mt-2 text-2xl font-bold leading-tight sm:text-3xl">{{ $product->name }}</h1>
            </div>

            <div class="flex flex-col gap-2">
                <p class="text-4xl font-bold tracking-tight">{{ Money::format($product->price_cents) }}</p>
                @if ($product->price_cents >= ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS)
                    <p class="text-sm font-semibold text-green-700">Frete grátis</p>
                @else
                    <p class="text-sm text-gray-600">Frete grátis em compras acima de {{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }} deste vendedor</p>
                @endif
                <p class="flex items-center gap-2 text-sm font-medium {{ $product->stock > 0 ? 'text-green-700' : 'text-red-700' }}">
                    <span class="h-2 w-2 rounded-full {{ $product->stock > 0 ? 'bg-green-600' : 'bg-red-600' }}" aria-hidden="true"></span>
                    @if ($product->stock <= 0)
                        Esgotado
                    @elseif ($product->stock <= 5)
                        Em estoque · só {{ $product->stock === 1 ? 'resta 1 unidade' : 'restam '.$product->stock.' unidades' }}
                    @else
                        Em estoque · {{ $product->stock }} unidades
                    @endif
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                @if ($isOwner)
                    <p class="text-sm text-gray-600">Este produto é seu.</p>
                    <a href="{{ route('seller.products.edit', $product) }}" class="mt-3 inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar produto</a>
                @elseif ($canPurchase)
                    <form wire:submit="addToCart" class="flex flex-col gap-4" novalidate>
                        <div class="flex items-center gap-3">
                            <x-input-label for="quantity" value="Quantidade" />
                            <x-text-input wire:model="quantity" id="quantity" type="number" min="1" max="{{ min($product->stock, \App\Models\CartItem::MAX_QUANTITY) }}" class="w-24" />
                        </div>
                        <x-input-error :messages="$errors->get('quantity')" />

                        <x-primary-button class="w-full py-3.5 text-base" wire:loading.attr="disabled" wire:target="addToCart">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                            <span wire:loading.remove wire:target="addToCart">Adicionar ao carrinho</span>
                            <span wire:loading wire:target="addToCart">Adicionando...</span>
                        </x-primary-button>
                    </form>

                    @if ($status)
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-green-50 p-3 text-sm text-green-800" role="status">
                            <span>{{ $status }}</span>
                            <a href="{{ route('cart') }}" class="font-semibold underline">Ver carrinho</a>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-600">Produto indisponível no momento.</p>
                    <a href="{{ route('home') }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-700 underline">Ver outros produtos</a>
                @endif
            </div>

            <ul class="flex flex-col gap-3 text-sm text-gray-600">
                <li class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    Pagamento seguro: o CVV nunca é armazenado
                </li>
                <li class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    Enviado pelo vendedor com código de rastreio
                </li>
            </ul>
        </div>
    </div>

    <section class="mt-12 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">
        <h2 class="text-lg font-bold">Descrição do produto</h2>
        {{-- Escaped output + nl2br over the escaped string: user HTML is never rendered. --}}
        <div class="mt-4 max-w-prose text-gray-700 leading-relaxed">{!! nl2br(e($product->description)) !!}</div>
    </section>
</div>
