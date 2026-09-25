@use('App\Support\Money')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header title="Minhas vendas" subtitle="Pedidos recebidos dos compradores. Envie e informe o código de rastreio.">
        <x-select-input wire:model.live="status" aria-label="Filtrar status">
            <option value="">Todos os status</option>
            @foreach ($statuses as $option)
                <option wire:key="st-{{ $option->value }}" value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </x-select-input>
    </x-page-header>

    <x-card class="overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Pedido</th>
                    <th class="px-4 py-3">Comprador</th>
                    <th class="px-4 py-3">Itens</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr wire:key="sale-{{ $order->id }}" class="border-t">
                        <td class="px-4 py-3"><a href="{{ route('orders.show', $order) }}" class="font-mono text-indigo-700 underline">#{{ \Illuminate\Support\Str::limit($order->code, 10, '…') }}</a><br><span class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</span></td>
                        <td class="px-4 py-3">{{ $order->buyer->name }}</td>
                        <td class="px-4 py-3">{{ $order->items->sum('quantity') }}</td>
                        <td class="px-4 py-3">{{ Money::format($order->total_cents) }}</td>
                        <td class="px-4 py-3"><x-order-status-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Nenhuma venda encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
