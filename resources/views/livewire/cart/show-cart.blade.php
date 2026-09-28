@use('App\Support\Money')
@use('App\Services\ShippingCalculator')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header title="Meu carrinho" subtitle="Cada vendedor envia o próprio pacote, por isso os itens aparecem separados por vendedor." />

    @if ($groups->isEmpty())
        <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <svg class="h-14 w-14 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
            <p class="text-lg font-bold">Seu carrinho está vazio</p>
            <p class="text-sm text-gray-600">Escolha um produto na loja e ele aparece aqui.</p>
            <a href="{{ route('home') }}" class="mt-2 inline-flex items-center rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700">Ver produtos</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
            <div class="flex flex-col gap-6 lg:col-span-2">
                @foreach ($groups as $group)
                    <section wire:key="seller-{{ $group['seller']->id }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-card">
                        <header class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3 sm:px-6">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm text-gray-700">Pacote de <strong class="text-gray-900">{{ $group['seller']->name }}</strong></p>
                                @if ($group['subtotal_cents'] >= ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS)
                                    <span class="rounded-md bg-brand-100 px-2 py-0.5 text-xs font-bold text-brand-800">Frete grátis neste pacote</span>
                                @else
                                    <span class="text-xs text-gray-600">Faltam <strong class="text-gray-900">{{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS - $group['subtotal_cents']) }}</strong> para o frete grátis</span>
                                @endif
                            </div>
                            @if ($group['subtotal_cents'] < ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS)
                                <progress class="progress-brand" value="{{ $group['subtotal_cents'] }}" max="{{ ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS }}" aria-label="Progresso para o frete grátis"></progress>
                            @endif
                        </header>

                        <ul class="divide-y divide-gray-200">
                            @foreach ($group['items'] as $item)
                                <li wire:key="item-{{ $item->id }}" class="flex gap-4 px-4 py-4 sm:px-6">
                                    <a href="{{ route('products.show', $item->product) }}" class="h-16 w-16 shrink-0 overflow-hidden rounded-lg sm:h-20 sm:w-20 border border-gray-200 bg-gray-100 flex items-center justify-center" tabindex="-1" aria-hidden="true">
                                        @if ($item->product->imageUrl())
                                            <img src="{{ $item->product->imageUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                        @else
                                            <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                        @endif
                                    </a>

                                    <div class="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('products.show', $item->product) }}" class="font-medium line-clamp-2 hover:text-brand-700 hover:underline">{{ $item->product->name }}</a>
                                            <p class="text-sm text-gray-500">{{ Money::format($item->product->price_cents) }} cada</p>
                                        </div>

                                        <div class="flex items-center gap-3 sm:gap-4">
                                            <x-quantity-stepper size="sm" :value="$item->quantity" :max="max(1, min($item->product->stock, \App\Models\CartItem::MAX_QUANTITY))"
                                                decrement="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" increment="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                :label="'Quantidade de '.$item->product->name" />
                                            <span class="flex-1 whitespace-nowrap text-right font-bold tabular-nums sm:flex-none sm:min-w-[7rem]">{{ Money::format($item->product->price_cents * $item->quantity) }}</span>
                                            <button type="button" wire:click="remove({{ $item->id }})" class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500" aria-label="Remover {{ $item->product->name }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <aside class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-card lg:sticky lg:top-6">
                <h2 class="text-lg font-extrabold tracking-tight">Resumo</h2>

                @php($itemCount = $groups->sum(fn ($group) => $group['items']->sum('quantity')))

                <dl class="flex flex-col gap-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Subtotal ({{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'itens' }})</dt>
                        <dd class="font-medium">{{ Money::format($subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Frete</dt>
                        <dd class="text-gray-600">Calculado no checkout</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-3 text-base">
                        <dt class="font-semibold">Total parcial</dt>
                        <dd class="text-2xl font-extrabold tabular-nums tracking-tight">{{ Money::format($subtotal) }}</dd>
                    </div>
                </dl>

                <a href="{{ route('checkout') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg bg-brand-600 px-5 font-bold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">Continuar para o pagamento</a>
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg text-sm font-semibold text-brand-700 hover:bg-brand-50">Continuar comprando</a>

                <p class="flex items-center gap-2 border-t border-gray-200 pt-4 text-xs text-gray-500">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                    Pagamento seguro: o CVV nunca é armazenado.
                </p>
            </aside>
        </div>
    @endif
</div>
