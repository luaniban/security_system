@use('App\Support\Money')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-seller-nav />

    <x-page-header title="Minhas vendas" subtitle="Pedidos que os compradores já pagaram. Envie cada pacote e informe o código de rastreio." />

    <div class="-mx-4 mb-6 overflow-x-auto px-4 sm:mx-0 sm:px-0" role="group" aria-label="Filtrar por status">
        <div class="inline-flex min-w-max gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-card">
            @foreach (['' => 'Todas'] + collect($statuses)->mapWithKeys(fn ($option) => [$option->value => $option === \App\Enums\OrderStatus::Paid ? 'Para enviar' : $option->label()])->all() as $value => $label)
                <button type="button" wire:key="st-{{ $value ?: 'all' }}" wire:click="$set('status', '{{ $value }}')" aria-pressed="{{ $status === $value ? 'true' : 'false' }}"
                        class="min-h-9 rounded-lg px-3 text-sm font-semibold transition duration-150 ease-out {{ $status === $value ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="space-y-3" wire:loading.class="opacity-60">
        @forelse ($orders as $order)
            <a wire:key="sale-{{ $order->id }}" href="{{ route('orders.show', $order) }}" class="group flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-card transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-lift sm:flex-row sm:items-center">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold text-gray-900 group-hover:text-brand-700">{{ $order->items->pluck('product_name')->implode(', ') }}</p>
                    <p class="mt-0.5 text-sm text-gray-600">{{ $order->items->sum('quantity') }} {{ $order->items->sum('quantity') === 1 ? 'item' : 'itens' }} para {{ $order->buyer->name }}, em {{ $order->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 sm:justify-end">
                    <x-order-status-badge :status="$order->status" />
                    <span class="w-28 text-right text-lg font-extrabold tabular-nums tracking-tight">{{ Money::format($order->total_cents) }}</span>
                    @if ($order->canBeShipped())
                        <span class="hidden min-h-10 items-center rounded-lg bg-brand-600 px-3 text-sm font-bold text-white sm:inline-flex">Enviar</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                <p class="text-lg font-bold">{{ $status === '' ? 'Nenhuma venda ainda' : 'Nenhuma venda com este status' }}</p>
                <p class="text-sm text-gray-600">{{ $status === '' ? 'Quando alguém comprar um produto seu, o pedido aparece aqui.' : 'Escolha outro filtro para ver as demais vendas.' }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
