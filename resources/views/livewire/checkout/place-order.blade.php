@use('App\Support\Money')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header title="Finalizar compra" />

    <form wire:submit="placeOrder" class="grid gap-6 lg:grid-cols-3" novalidate autocomplete="off">
        <div class="min-w-0 lg:col-span-2 space-y-6">
            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="text-lg font-bold">1. Endereço de entrega</h2>
                    <a href="{{ route('addresses.index') }}" class="text-sm text-indigo-700 underline">Gerenciar</a>
                </div>

                @forelse ($addresses as $address)
                    <label wire:key="addr-{{ $address->id }}" class="flex items-start gap-3 border rounded-lg p-3 mb-2 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" wire:model.live="addressId" value="{{ $address->id }}" class="mt-1 text-indigo-600">
                        <span class="text-sm">
                            <strong>{{ $address->label }}</strong> — {{ $address->recipient_name }}<br>
                            {{ $address->street }}, {{ $address->number }} · {{ $address->city }}/{{ $address->state->value }} · CEP {{ $address->formattedZipCode() }}
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-gray-600">Cadastre um endereço antes de comprar. <a class="underline" href="{{ route('addresses.index') }}">Adicionar endereço</a></p>
                @endforelse
                <x-input-error :messages="$errors->get('addressId')" class="mt-2" />
            </x-card>

            <x-card>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="text-lg font-bold">2. Pagamento</h2>
                    <a href="{{ route('cards.index') }}" class="text-sm text-indigo-700 underline">Gerenciar cartões</a>
                </div>

                @forelse ($cards as $card)
                    <label wire:key="card-{{ $card->id }}" class="flex items-center gap-3 border rounded-lg p-3 mb-2 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" wire:model="cardId" value="{{ $card->id }}" class="text-indigo-600" @disabled($card->isExpired())>
                        <span class="flex min-w-0 flex-1 flex-wrap items-baseline justify-between gap-x-3">
                            <span class="text-sm font-mono whitespace-nowrap">{{ $card->brand->label() }} {{ $card->maskedNumber() }}</span>
                            <span class="text-xs text-gray-500 whitespace-nowrap">{{ $card->expiry() }} {{ $card->isExpired() ? '(vencido)' : '' }}</span>
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-gray-600">Nenhum cartão salvo. <a class="underline" href="{{ route('cards.index') }}">Adicionar cartão</a></p>
                @endforelse
                <x-input-error :messages="$errors->get('cardId')" class="mt-2" />

                <div class="mt-4 w-32">
                    <x-input-label for="cvv" value="CVV" />
                    <x-text-input wire:model="cvv" id="cvv" type="password" inputmode="numeric" maxlength="4" class="mt-1 block w-full" autocomplete="cc-csc" />
                    <x-input-error :messages="$errors->get('cvv')" class="mt-2" />
                </div>
                <p class="mt-2 text-xs text-gray-500">O CVV é usado apenas nesta transação e nunca é armazenado.</p>
            </x-card>
        </div>

        <x-card class="h-fit space-y-3 lg:sticky lg:top-6">
            <h2 class="text-lg font-bold">Resumo do pedido</h2>

            @foreach ($groups as $group)
                <div wire:key="summary-{{ $group['seller']->id }}" class="border-t pt-3 text-sm">
                    <p class="text-gray-500 mb-1">Pacote de {{ $group['seller']->name }}</p>
                    @foreach ($group['items'] as $item)
                        <div wire:key="summary-item-{{ $item->id }}" class="flex justify-between gap-3">
                            <span class="min-w-0">{{ $item->quantity }}× {{ \Illuminate\Support\Str::limit($item->product->name, 28) }}</span>
                            <span class="shrink-0 whitespace-nowrap">{{ Money::format($item->product->price_cents * $item->quantity) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-gray-600">
                        <span>Frete</span>
                        <span>{{ $hasAddress ? ($group['shipping_cents'] === 0 ? 'Grátis' : Money::format($group['shipping_cents'])) : '—' }}</span>
                    </div>
                </div>
            @endforeach

            <div class="border-t pt-3 text-sm space-y-1">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ Money::format($subtotal) }}</span></div>
                <div class="flex justify-between"><span>Frete</span><span>{{ $hasAddress ? Money::format($shipping) : 'selecione o endereço' }}</span></div>
                <div class="flex justify-between text-lg font-semibold"><span>Total</span><span>{{ Money::format($total) }}</span></div>
            </div>

            <p class="text-xs text-gray-500">Frete grátis por vendedor acima de <span class="whitespace-nowrap">{{ Money::format(\App\Services\ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }}</span>.</p>

            @error('payment')
                <p class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
            @enderror

            <x-primary-button class="w-full justify-center py-3" wire:loading.attr="disabled" :disabled="$addresses->isEmpty() || $cards->isEmpty()">
                <span wire:loading.remove wire:target="placeOrder">Pagar {{ Money::format($total) }}</span>
                <span wire:loading wire:target="placeOrder">Processando...</span>
            </x-primary-button>
        </x-card>
    </form>
</div>
