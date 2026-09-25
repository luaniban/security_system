@use('App\Support\Money')

<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header title="Minhas compras" />

    <div class="space-y-4">
        @forelse ($orders as $order)
            <a wire:key="order-{{ $order->id }}" href="{{ route('orders.show', $order) }}" class="block bg-white rounded-lg shadow-sm p-5 hover:ring-2 hover:ring-indigo-200">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-mono text-sm text-gray-500">Pedido #{{ $order->code }}</p>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }} · vendedor {{ $order->seller->name }}</p>
                    </div>
                    <x-order-status-badge :status="$order->status" />
                </div>
                <p class="mt-2 text-sm text-gray-700">{{ $order->items->pluck('product_name')->implode(', ') }}</p>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-gray-500">Previsão de entrega: {{ $order->estimated_delivery_date->format('d/m/Y') }}</span>
                    <span class="font-semibold">{{ Money::format($order->total_cents) }}</span>
                </div>
            </a>
        @empty
            <x-card><p class="text-gray-600">Nenhuma compra ainda.</p></x-card>
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
