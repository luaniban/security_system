@use('App\Support\Money')
@use('App\Enums\OrderStatus')

<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4">
    <a href="{{ $isSeller ? route('seller.sales.index') : route('orders.index') }}" class="inline-flex min-h-10 items-center gap-1 rounded-lg text-sm font-semibold text-brand-700 hover:underline">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        {{ $isSeller ? 'Minhas vendas' : 'Minhas compras' }}
    </a>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $isSeller ? 'Venda para '.$order->buyer->name : 'Compra de '.$order->seller->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">Pedido <span class="break-all tabular-nums">{{ mb_strtoupper($order->code) }}</span>, feito em {{ $order->created_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
        <x-order-status-badge :status="$order->status" class="text-sm" />
    </div>

    @error('order')
        <p class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
    @enderror

    {{-- Next step first: what this person has to do now. --}}
    @if ($isSeller && $order->canBeShipped())
        <x-card class="mt-6 border-amber-200 bg-amber-50">
            <h2 class="text-lg font-extrabold tracking-tight">Envie este pedido</h2>
            <p class="mt-1 text-sm text-gray-700">O comprador já pagou. Poste o pacote nos Correios e informe o código de rastreio para ele acompanhar a entrega.</p>
            <form wire:submit="ship" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" novalidate>
                <div class="sm:w-64">
                    <x-input-label for="trackingCode" value="Código de rastreio" />
                    <x-text-input wire:model="trackingCode" id="trackingCode" class="mt-1 block w-full font-mono uppercase" maxlength="13" placeholder="AA123456789BR" autocomplete="off" />
                </div>
                <x-primary-button wire:loading.attr="disabled" wire:target="ship">Marcar como enviado</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('trackingCode')" class="mt-2" />
        </x-card>
    @endif

    @if ($isBuyer && $order->canBeMarkedDelivered())
        <x-card class="mt-6 flex flex-col gap-4 border-brand-200 bg-brand-50 sm:flex-row sm:items-center">
            <div class="flex-1">
                <h2 class="text-lg font-extrabold tracking-tight">Seu pacote está a caminho</h2>
                <p class="mt-1 text-sm text-gray-700">Quando ele chegar, confirme o recebimento para concluir o pedido.</p>
            </div>
            <x-primary-button type="button" wire:click="confirmDelivery" wire:confirm="Confirmar que você recebeu o pedido?">Confirmar recebimento</x-primary-button>
        </x-card>
    @endif

    <x-card class="mt-6">
        <h2 class="mb-5 text-lg font-extrabold tracking-tight">Acompanhamento da entrega</h2>
        @php
            $steps = [
                ['label' => 'Pagamento aprovado', 'at' => $order->paid_at],
                ['label' => 'Enviado', 'at' => $order->shipped_at],
                ['label' => 'Entregue', 'at' => $order->delivered_at],
            ];
        @endphp

        @if ($order->status === OrderStatus::Cancelled)
            <p class="text-sm text-gray-700">Pedido cancelado em {{ $order->cancelled_at?->format('d/m/Y \à\s H:i') }}. O estoque foi devolvido e o valor estornado no cartão.</p>
        @else
            <ol class="grid gap-4 sm:grid-cols-3">
                @foreach ($steps as $step)
                    <li wire:key="step-{{ $loop->index }}" class="flex items-center gap-3 sm:flex-col sm:items-start">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $step['at'] ? 'bg-brand-600 text-white' : 'border-2 border-dashed border-gray-300 text-gray-500' }}">
                            @if ($step['at'])
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <div>
                            <p class="text-sm font-bold {{ $step['at'] ? 'text-gray-900' : 'text-gray-600' }}">{{ $step['label'] }}</p>
                            <p class="text-xs text-gray-600">{{ $step['at']?->format('d/m/Y \à\s H:i') ?? 'Aguardando' }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-5 flex flex-col gap-1 rounded-xl bg-gray-50 p-4 text-sm text-gray-700 sm:flex-row sm:gap-6">
                <p>Previsão de entrega: <strong class="text-gray-900">{{ $order->estimated_delivery_date->format('d/m/Y') }}</strong></p>
                @if ($order->tracking_code)
                    <p>Código de rastreio: <span class="font-mono font-semibold text-gray-900">{{ $order->tracking_code }}</span></p>
                @endif
            </div>
        @endif
    </x-card>

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        <x-card class="md:col-span-2">
            <h2 class="mb-3 text-lg font-extrabold tracking-tight">Itens</h2>
            @foreach ($order->items as $item)
                <div wire:key="item-{{ $item->id }}" class="flex justify-between border-t py-2 text-sm">
                    <span>{{ $item->quantity }}× {{ $item->product_name }}</span>
                    <span class="tabular-nums">{{ Money::format($item->lineTotalCents()) }}</span>
                </div>
            @endforeach
            <div class="border-t pt-3 mt-2 text-sm space-y-1">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ Money::format($order->subtotal_cents) }}</span></div>
                <div class="flex justify-between"><span>Frete</span><span>{{ $order->shipping_cents ? Money::format($order->shipping_cents) : 'Grátis' }}</span></div>
                <div class="flex items-baseline justify-between pt-1"><span class="font-bold">Total</span><span class="text-xl font-extrabold tabular-nums tracking-tight">{{ Money::format($order->total_cents) }}</span></div>
            </div>
            @if ($isBuyer)
                <p class="mt-3 text-xs text-gray-600">Pago com {{ \App\Enums\CardBrand::from($order->card_brand)->label() }} final {{ $order->card_last_four }}</p>
            @endif
        </x-card>

        <x-card>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight">Endereço de entrega</h2>
            @php($address = $order->shipping_address)
            <p class="text-sm font-semibold">{{ $address['recipient_name'] }}</p>
            <p class="text-sm text-gray-600">{{ $address['street'] }}, {{ $address['number'] }}{{ $address['complement'] ? ' - '.$address['complement'] : '' }}</p>
            <p class="text-sm text-gray-600">{{ $address['district'] }}, {{ $address['city'] }}/{{ $address['state'] }}</p>
            <p class="text-sm text-gray-600">CEP {{ $address['zip_code'] }}</p>
        </x-card>
    </div>

    @if ($order->canBeCancelled())
        <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-6">
            <p class="text-sm text-gray-600">Mudou de ideia? Dá para cancelar enquanto o pedido não foi enviado. O valor é estornado no cartão.</p>
            <x-secondary-button type="button" wire:click="cancel" wire:confirm="Cancelar este pedido? O valor será estornado." class="text-red-700 hover:bg-red-50">Cancelar pedido</x-secondary-button>
        </div>
    @endif
</div>
