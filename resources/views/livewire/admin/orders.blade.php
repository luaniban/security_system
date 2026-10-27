<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-admin-nav />

    <x-page-header title="Pedidos" subtitle="Todas as transações da loja. O endereço de entrega fica de fora (minimização de dados).">
        <x-select-input wire:model.live="status" aria-label="Filtrar status">
            <option value="">Todos os status</option>
            @foreach ($statuses as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </x-select-input>
    </x-page-header>

    <dl class="mb-4 flex flex-wrap gap-x-8 gap-y-1 text-sm text-gray-600">
        <div><dt class="inline">Pedidos no total:</dt> <dd class="inline font-semibold text-gray-900 tabular-nums">{{ $orderCount }}</dd></div>
        <div><dt class="inline">Movimentado (sem cancelados):</dt> <dd class="inline font-semibold text-gray-900 tabular-nums">{{ \App\Support\Money::format($revenueCents) }}</dd></div>
    </dl>

    <x-card class="overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Pedido</th>
                    <th class="px-4 py-3">Data</th>
                    <th class="px-4 py-3">Comprador</th>
                    <th class="px-4 py-3">Vendedor</th>
                    <th class="px-4 py-3">Itens</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3">Pagamento</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr wire:key="order-{{ $order->id }}" class="border-t align-top">
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs">{{ $order->code }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $order->buyer->name }}<br><span class="text-xs text-gray-500">{{ $order->buyer->email }}</span></td>
                        <td class="px-4 py-3">{{ $order->seller->name }}</td>
                        <td class="px-4 py-3">
                            @foreach ($order->items as $item)
                                <div>{{ $item->quantity }}× {{ $item->product_name }}</div>
                            @endforeach
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ \App\Support\Money::format($order->total_cents) }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ ucfirst($order->card_brand) }} •••• {{ $order->card_last_four }}</td>
                        <td class="px-4 py-3"><x-order-status-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">Nenhum pedido encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
