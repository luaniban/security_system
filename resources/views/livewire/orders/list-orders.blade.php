@use('App\Support\Money')

<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-account-nav />

    <x-page-header title="Minhas compras" />

    <div class="space-y-4">
        @forelse ($orders as $order)
            <a wire:key="order-{{ $order->id }}" href="{{ route('orders.show', $order) }}" class="group block rounded-2xl border border-gray-200 bg-white p-5 shadow-card transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lift focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-bold text-gray-900 group-hover:text-brand-700">{{ $order->items->pluck('product_name')->implode(', ') }}</p>
                        <p class="mt-0.5 text-sm text-gray-600">Comprado em {{ $order->created_at->format('d/m/Y') }}, vendido por {{ $order->seller->name }}</p>
                    </div>
                    <x-order-status-badge :status="$order->status" />
                </div>
                <div class="mt-4 flex flex-wrap items-end justify-between gap-2 border-t border-gray-100 pt-3 text-sm">
                    <span class="text-gray-600">
                        @if ($order->status === \App\Enums\OrderStatus::Delivered)
                            Entregue em {{ $order->delivered_at?->format('d/m/Y') }}
                        @elseif ($order->status === \App\Enums\OrderStatus::Cancelled)
                            Cancelado em {{ $order->cancelled_at?->format('d/m/Y') }}
                        @else
                            Chega até <strong class="text-gray-900">{{ $order->estimated_delivery_date->format('d/m/Y') }}</strong>
                        @endif
                    </span>
                    <span class="text-lg font-extrabold tabular-nums tracking-tight">{{ Money::format($order->total_cents) }}</span>
                </div>
            </a>
        @empty
            <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                <p class="text-lg font-bold">Você ainda não fez nenhuma compra</p>
                <p class="text-sm text-gray-600">Quando comprar, você acompanha a entrega de cada pacote por aqui.</p>
                <a href="{{ route('home') }}" class="mt-2 inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-5 text-sm font-bold text-white hover:bg-brand-700">Ver produtos</a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
