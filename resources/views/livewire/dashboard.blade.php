@use('App\Support\Money')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header :title="'Olá, '.\Illuminate\Support\Str::before(auth()->user()->name, ' ')" subtitle="Acompanhe suas compras e vendas e acesse os atalhos da sua conta." />

    @if ($salesToShip > 0)
        <a href="{{ route('seller.sales.index', ['status' => 'paid']) }}" class="mb-8 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-card transition duration-150 ease-out hover:border-amber-300">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-300 text-gray-900" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </span>
            <span class="flex-1">
                <span class="block font-bold text-gray-900">{{ $salesToShip === 1 ? 'Você tem 1 venda para enviar' : "Você tem {$salesToShip} vendas para enviar" }}</span>
                <span class="block text-sm text-gray-700">O comprador já pagou. Envie o pacote e informe o código de rastreio.</span>
            </span>
            <span class="hidden text-sm font-bold text-amber-900 sm:block">Ver vendas</span>
        </a>
    @endif

    <div class="mb-10 grid gap-4 sm:grid-cols-3">
        <a href="{{ $cartCount > 0 ? route('cart') : route('home') }}" class="group flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-card transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lift">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
            </span>
            <span>
                <span class="block font-bold text-gray-900 group-hover:text-brand-700">{{ $cartCount > 0 ? 'Finalizar carrinho' : 'Ir às compras' }}</span>
                <span class="block text-sm text-gray-600">{{ $cartCount > 0 ? ($cartCount === 1 ? '1 item esperando por você' : "{$cartCount} itens esperando por você") : 'Veja os produtos à venda' }}</span>
            </span>
        </a>
        <a href="{{ route('orders.index') }}" class="group flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-card transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lift">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
            </span>
            <span>
                <span class="block font-bold text-gray-900 group-hover:text-brand-700">Acompanhar entregas</span>
                <span class="block text-sm text-gray-600">{{ $openPurchases > 0 ? ($openPurchases === 1 ? '1 pedido a caminho' : "{$openPurchases} pedidos a caminho") : 'Nenhum pedido em andamento' }}</span>
            </span>
        </a>
        <a href="{{ $activeProducts > 0 ? route('seller.products.index') : route('seller.products.create') }}" class="group flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-card transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lift">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" /></svg>
            </span>
            <span>
                <span class="block font-bold text-gray-900 group-hover:text-brand-700">{{ $activeProducts > 0 ? 'Minha loja' : 'Anunciar um produto' }}</span>
                <span class="block text-sm text-gray-600">{{ $activeProducts > 0 ? ($activeProducts === 1 ? '1 anúncio ativo' : "{$activeProducts} anúncios ativos") : 'Venda o que você não usa mais' }}</span>
            </span>
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
        <x-card class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-extrabold tracking-tight">Compras recentes</h2>
                @if ($recentPurchases->isNotEmpty())
                    <a href="{{ route('orders.index') }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">Ver todas</a>
                @endif
            </div>

            <ul class="divide-y divide-gray-200">
                @forelse ($recentPurchases as $order)
                    <li wire:key="order-{{ $order->id }}">
                        <a href="{{ route('orders.show', $order) }}" class="-mx-2 flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg px-2 py-3 hover:bg-gray-50">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold text-gray-900">{{ $order->items->pluck('product_name')->implode(', ') }}</span>
                                <span class="block text-xs text-gray-600">{{ $order->created_at->format('d/m/Y') }}</span>
                            </span>
                            <x-order-status-badge :status="$order->status" />
                            <span class="w-28 text-right font-bold tabular-nums">{{ Money::format($order->total_cents) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="py-6 text-center">
                        <p class="font-semibold text-gray-900">Você ainda não comprou nada.</p>
                        <a href="{{ route('home') }}" class="mt-3 inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-4 text-sm font-bold text-white hover:bg-brand-700">Ver produtos</a>
                    </li>
                @endforelse
            </ul>
        </x-card>

        <x-card>
            <h2 class="mb-2 text-lg font-extrabold tracking-tight">Minha conta</h2>
            <ul class="divide-y divide-gray-200 text-sm">
                <li><a href="{{ route('profile.edit') }}" class="flex min-h-12 items-center justify-between font-semibold text-gray-800 hover:text-brand-700">Dados pessoais e senha</a></li>
                <li><a href="{{ route('addresses.index') }}" class="flex min-h-12 items-center justify-between font-semibold text-gray-800 hover:text-brand-700">Endereços <span class="font-normal text-gray-600">{{ $addressCount }}</span></a></li>
                <li><a href="{{ route('cards.index') }}" class="flex min-h-12 items-center justify-between font-semibold text-gray-800 hover:text-brand-700">Cartões <span class="font-normal text-gray-600">{{ $cardCount }}</span></a></li>
                <li><a href="{{ route('seller.sales.index') }}" class="flex min-h-12 items-center justify-between font-semibold text-gray-800 hover:text-brand-700">Minhas vendas @if ($salesToShip > 0)<span class="rounded-full bg-amber-100 px-2 text-xs font-bold text-amber-900">{{ $salesToShip }} para enviar</span>@endif</a></li>
            </ul>
        </x-card>
    </div>
</div>
