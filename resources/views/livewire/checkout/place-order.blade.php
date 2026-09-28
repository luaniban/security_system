@use('App\Support\Money')
@use('App\Services\ShippingCalculator')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    {{-- Checkout is a real sequence, so the steps are numbered. --}}
    <nav aria-label="Etapas da compra" class="mb-6">
        <ol class="flex items-center gap-2 text-sm font-semibold">
            <li><a href="{{ route('cart') }}" class="flex items-center gap-2 text-brand-700 hover:underline">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white" aria-hidden="true">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                </span>Carrinho</a></li>
            <li class="h-px w-6 bg-gray-300 sm:w-10" aria-hidden="true"></li>
            <li class="flex items-center gap-2 text-gray-900" aria-current="step">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-900 text-xs text-white">2</span>Entrega e pagamento</li>
            <li class="h-px w-6 bg-gray-300 sm:w-10" aria-hidden="true"></li>
            <li class="flex items-center gap-2 text-gray-500">
                <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-300 text-xs">3</span><span class="hidden sm:inline">Confirmação</span></li>
        </ol>
    </nav>

    <x-page-header title="Finalizar compra" />

    <form wire:submit="placeOrder" class="grid gap-6 lg:grid-cols-3 lg:items-start" novalidate autocomplete="off">
        <div class="min-w-0 space-y-6 lg:col-span-2">
            <x-card>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-extrabold tracking-tight">Onde entregar</h2>
                    @if ($addresses->isNotEmpty())
                        <a href="{{ route('addresses.index') }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">Novo endereço</a>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Endereço de entrega">
                    @forelse ($addresses as $address)
                        <label wire:key="addr-{{ $address->id }}" class="relative flex cursor-pointer gap-3 rounded-xl border border-gray-200 p-4 transition duration-150 ease-out hover:border-gray-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-500">
                            <input type="radio" wire:model.live="addressId" value="{{ $address->id }}" class="mt-0.5 h-5 w-5 border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm leading-relaxed">
                                <span class="block font-bold text-gray-900">{{ $address->label }}</span>
                                <span class="block text-gray-700">{{ $address->recipient_name }}</span>
                                <span class="block text-gray-600">{{ $address->street }}, {{ $address->number }}</span>
                                <span class="block text-gray-600">{{ $address->city }}/{{ $address->state->value }}, CEP {{ $address->formattedZipCode() }}</span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 p-5 text-sm sm:col-span-2">
                            <p class="font-semibold text-gray-900">Você ainda não tem endereço cadastrado.</p>
                            <p class="mt-1 text-gray-600">Cadastre um endereço para calcularmos o frete.</p>
                            <a href="{{ route('addresses.index') }}" class="mt-3 inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-4 font-bold text-white hover:bg-brand-700">Cadastrar endereço</a>
                        </div>
                    @endforelse
                </div>
                <x-input-error :messages="$errors->get('addressId')" class="mt-2" />
            </x-card>

            <x-card>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-extrabold tracking-tight">Como pagar</h2>
                    @if ($cards->isNotEmpty())
                        <a href="{{ route('cards.index') }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">Novo cartão</a>
                    @endif
                </div>

                <div class="grid gap-3" role="radiogroup" aria-label="Cartão de crédito">
                    @forelse ($cards as $card)
                        <label wire:key="card-{{ $card->id }}" class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-4 transition duration-150 ease-out hover:border-gray-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
                            <input type="radio" wire:model="cardId" value="{{ $card->id }}" class="h-5 w-5 border-gray-300 text-brand-600 focus:ring-brand-500" @disabled($card->isExpired())>
                            <span class="flex min-w-0 flex-1 flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                <span class="text-sm font-bold tabular-nums whitespace-nowrap text-gray-900">{{ $card->brand->label() }} final {{ $card->last_four }}</span>
                                <span class="text-xs whitespace-nowrap {{ $card->isExpired() ? 'font-semibold text-red-700' : 'text-gray-600' }}">{{ $card->isExpired() ? 'Vencido em '.$card->expiry() : 'Válido até '.$card->expiry() }}</span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 p-5 text-sm">
                            <p class="font-semibold text-gray-900">Nenhum cartão salvo.</p>
                            <p class="mt-1 text-gray-600">Adicione um cartão de crédito para pagar.</p>
                            <a href="{{ route('cards.index') }}" class="mt-3 inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-4 font-bold text-white hover:bg-brand-700">Adicionar cartão</a>
                        </div>
                    @endforelse
                </div>
                <x-input-error :messages="$errors->get('cardId')" class="mt-2" />

                @if ($cards->isNotEmpty())
                    <div class="mt-5 flex flex-col gap-2 border-t border-gray-200 pt-5 sm:flex-row sm:items-end sm:gap-4">
                        <div class="w-32">
                            <x-input-label for="cvv" value="CVV" />
                            <x-text-input wire:model="cvv" id="cvv" type="password" inputmode="numeric" maxlength="4" class="mt-1 block w-full tabular-nums" autocomplete="cc-csc" aria-describedby="cvv-help" />
                        </div>
                        <p id="cvv-help" class="flex-1 text-sm text-gray-600 sm:pb-2">Os 3 dígitos no verso do cartão. Usado só nesta compra e nunca guardado.</p>
                    </div>
                    <x-input-error :messages="$errors->get('cvv')" class="mt-2" />
                @endif
            </x-card>
        </div>

        <x-card class="space-y-4 lg:sticky lg:top-6">
            <h2 class="text-lg font-extrabold tracking-tight">Resumo</h2>

            @foreach ($groups as $group)
                <div wire:key="summary-{{ $group['seller']->id }}" class="space-y-1 border-t border-gray-200 pt-3 text-sm">
                    <p class="mb-1 text-xs font-semibold text-gray-600">Pacote de {{ $group['seller']->name }}</p>
                    @foreach ($group['items'] as $item)
                        <div wire:key="summary-item-{{ $item->id }}" class="flex justify-between gap-3">
                            <span class="min-w-0 text-gray-800">{{ $item->quantity }}× {{ \Illuminate\Support\Str::limit($item->product->name, 28) }}</span>
                            <span class="shrink-0 whitespace-nowrap tabular-nums">{{ Money::format($item->product->price_cents * $item->quantity) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-gray-600">
                        <span>Frete</span>
                        <span class="tabular-nums {{ $hasAddress && $group['shipping_cents'] === 0 ? 'font-bold text-brand-700' : '' }}">{{ $hasAddress ? ($group['shipping_cents'] === 0 ? 'Grátis' : Money::format($group['shipping_cents'])) : 'escolha o endereço' }}</span>
                    </div>
                </div>
            @endforeach

            <dl class="space-y-1 border-t border-gray-200 pt-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd class="tabular-nums">{{ Money::format($subtotal) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-600">Frete</dt><dd class="tabular-nums">{{ $hasAddress ? Money::format($shipping) : 'escolha o endereço' }}</dd></div>
                <div class="flex items-baseline justify-between pt-2"><dt class="text-base font-bold">Total</dt><dd class="text-2xl font-extrabold tabular-nums tracking-tight">{{ Money::format($total) }}</dd></div>
            </dl>

            @error('payment')
                <p class="flex gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800" role="alert">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    {{ $message }}
                </p>
            @enderror

            <x-primary-button class="min-h-12 w-full text-base" wire:loading.attr="disabled" :disabled="$addresses->isEmpty() || $cards->isEmpty()">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                <span wire:loading.remove wire:target="placeOrder">Pagar {{ Money::format($total) }}</span>
                <span wire:loading wire:target="placeOrder">Processando pagamento...</span>
            </x-primary-button>

            <p class="text-center text-xs text-gray-600">Frete grátis em cada vendedor a partir de <span class="whitespace-nowrap">{{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }}</span>.</p>
        </x-card>
    </form>
</div>
