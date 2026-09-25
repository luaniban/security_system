@use('App\Support\Money')

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header :title="'Olá, '.auth()->user()->name" subtitle="Resumo da sua conta" />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <a href="{{ route('seller.sales.index') }}" class="bg-white rounded-lg shadow-sm p-5 hover:ring-2 hover:ring-indigo-200">
            <p class="text-sm text-gray-500">Vendas aguardando envio</p>
            <p class="mt-1 text-3xl font-semibold">{{ $salesToShip }}</p>
        </a>
        <a href="{{ route('seller.products.index') }}" class="bg-white rounded-lg shadow-sm p-5 hover:ring-2 hover:ring-indigo-200">
            <p class="text-sm text-gray-500">Produtos ativos</p>
            <p class="mt-1 text-3xl font-semibold">{{ $activeProducts }}</p>
        </a>
        <a href="{{ route('addresses.index') }}" class="bg-white rounded-lg shadow-sm p-5 hover:ring-2 hover:ring-indigo-200">
            <p class="text-sm text-gray-500">Endereços cadastrados</p>
            <p class="mt-1 text-3xl font-semibold">{{ $addressCount }}</p>
        </a>
        <a href="{{ route('cards.index') }}" class="bg-white rounded-lg shadow-sm p-5 hover:ring-2 hover:ring-indigo-200">
            <p class="text-sm text-gray-500">Cartões salvos</p>
            <p class="mt-1 text-3xl font-semibold">{{ $cardCount }}</p>
        </a>
    </div>

    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Compras recentes</h2>
            <a href="{{ route('orders.index') }}" class="text-sm text-indigo-700 underline">Ver todas</a>
        </div>

        @forelse ($recentPurchases as $order)
            <a wire:key="order-{{ $order->id }}" href="{{ route('orders.show', $order) }}" class="flex items-center justify-between border-t py-3 hover:bg-gray-50">
                <span class="font-mono text-sm">#{{ $order->code }}</span>
                <span class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                <x-order-status-badge :status="$order->status" />
                <span class="font-medium">{{ Money::format($order->total_cents) }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-500">Você ainda não fez nenhuma compra. <a href="{{ route('home') }}" class="underline">Ir para a loja</a>.</p>
        @endforelse
    </x-card>
</div>
