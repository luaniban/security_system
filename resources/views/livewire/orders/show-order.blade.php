@use('App\Support\Money')
@use('App\Enums\OrderStatus')

<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4">
    <a href="{{ $isSeller ? route('seller.sales.index') : route('orders.index') }}" class="text-sm text-indigo-700 underline">&larr; Voltar</a>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Pedido <span class="font-mono">#{{ $order->code }}</span></h1>
            <p class="text-sm text-gray-500">Realizado em {{ $order->created_at->format('d/m/Y H:i') }} · {{ $isSeller ? 'Comprador: '.$order->buyer->name : 'Vendedor: '.$order->seller->name }}</p>
        </div>
        <x-order-status-badge :status="$order->status" class="text-sm" />
    </div>

    @error('order')
        <p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
    @enderror

    {{-- Delivery timeline --}}
    <x-card class="mt-6">
        <h2 class="text-lg font-medium mb-4">Acompanhamento da entrega</h2>
        @php
            $steps = [
                ['label' => 'Pagamento aprovado', 'at' => $order->paid_at],
                ['label' => 'Enviado', 'at' => $order->shipped_at],
                ['label' => 'Entregue', 'at' => $order->delivered_at],
            ];
        @endphp

        @if ($order->status === OrderStatus::Cancelled)
            <p class="text-sm text-gray-700">Pedido cancelado em {{ $order->cancelled_at?->format('d/m/Y H:i') }}. O estoque foi devolvido e o valor estornado.</p>
        @else
            <ol class="grid gap-4 sm:grid-cols-3">
                @foreach ($steps as $step)
                    <li wire:key="step-{{ $loop->index }}" class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $step['at'] ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500' }}">{{ $loop->iteration }}</span>
                        <div>
                            <p class="text-sm font-medium">{{ $step['label'] }}</p>
                            <p class="text-xs text-gray-500">{{ $step['at']?->format('d/m/Y H:i') ?? 'pendente' }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <p class="mt-4 text-sm text-gray-600">
                Previsão de entrega: <strong>{{ $order->estimated_delivery_date->format('d/m/Y') }}</strong>
                @if ($order->tracking_code)
                    · Código de rastreio: <span class="font-mono">{{ $order->tracking_code }}</span>
                @endif
            </p>
        @endif
    </x-card>

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        <x-card class="md:col-span-2">
            <h2 class="text-lg font-medium mb-3">Itens</h2>
            @foreach ($order->items as $item)
                <div wire:key="item-{{ $item->id }}" class="flex justify-between border-t py-2 text-sm">
                    <span>{{ $item->quantity }}× {{ $item->product_name }}</span>
                    <span>{{ Money::format($item->lineTotalCents()) }}</span>
                </div>
            @endforeach
            <div class="border-t pt-3 mt-2 text-sm space-y-1">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ Money::format($order->subtotal_cents) }}</span></div>
                <div class="flex justify-between"><span>Frete</span><span>{{ $order->shipping_cents ? Money::format($order->shipping_cents) : 'Grátis' }}</span></div>
                <div class="flex justify-between font-semibold text-base"><span>Total</span><span>{{ Money::format($order->total_cents) }}</span></div>
            </div>
            @if ($isBuyer)
                <p class="mt-3 text-xs text-gray-500">Pago com {{ \App\Enums\CardBrand::from($order->card_brand)->label() }} final {{ $order->card_last_four }}</p>
            @endif
        </x-card>

        <x-card>
            <h2 class="text-lg font-medium mb-3">Entregar para</h2>
            @php($address = $order->shipping_address)
            <p class="text-sm">{{ $address['recipient_name'] }}</p>
            <p class="text-sm text-gray-600">{{ $address['street'] }}, {{ $address['number'] }}{{ $address['complement'] ? ' - '.$address['complement'] : '' }}</p>
            <p class="text-sm text-gray-600">{{ $address['district'] }} · {{ $address['city'] }}/{{ $address['state'] }}</p>
            <p class="text-sm text-gray-600">CEP {{ $address['zip_code'] }}</p>
        </x-card>
    </div>

    <div class="mt-6 flex flex-wrap gap-4">
        @if ($isSeller && $order->canBeShipped())
            <x-card class="flex-1">
                <h2 class="text-lg font-medium mb-3">Enviar pedido</h2>
                <form wire:submit="ship" class="flex flex-wrap items-end gap-3" novalidate>
                    <div>
                        <x-input-label for="trackingCode" value="Código de rastreio" />
                        <x-text-input wire:model="trackingCode" id="trackingCode" class="mt-1 font-mono uppercase" maxlength="13" placeholder="AA123456789BR" />
                    </div>
                    <x-primary-button wire:loading.attr="disabled">Marcar como enviado</x-primary-button>
                </form>
                <x-input-error :messages="$errors->get('trackingCode')" class="mt-2" />
            </x-card>
        @endif

        @if ($isBuyer && $order->canBeMarkedDelivered())
            <x-primary-button type="button" wire:click="confirmDelivery" wire:confirm="Confirmar que você recebeu o pedido?">Confirmar recebimento</x-primary-button>
        @endif

        @if ($order->canBeCancelled())
            <x-danger-button type="button" wire:click="cancel" wire:confirm="Cancelar este pedido? O valor será estornado.">Cancelar pedido</x-danger-button>
        @endif
    </div>
</div>
