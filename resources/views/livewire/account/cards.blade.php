<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-account-nav />

    <x-page-header title="Cartões de crédito" subtitle="Número criptografado (AES-256). O CVV nunca é armazenado.">
        @unless ($showForm)
            <x-primary-button type="button" wire:click="create">Adicionar cartão</x-primary-button>
        @endunless
    </x-page-header>

    @if (session('card-status'))
        <p class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('card-status') }}</p>
    @endif

    @if ($showForm)
        <x-card class="mb-6">
            <h2 class="text-lg font-medium mb-4">Novo cartão</h2>

            <form wire:submit="save" class="grid gap-4 sm:grid-cols-4" novalidate autocomplete="off">
                <div class="sm:col-span-4">
                    <x-input-label for="card_number" value="Número do cartão" />
                    <x-text-input wire:model="card_number" id="card_number" class="mt-1 block w-full font-mono" inputmode="numeric" maxlength="23" placeholder="0000 0000 0000 0000" autocomplete="cc-number" />
                    <x-input-error :messages="$errors->get('card_number')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="holder_name" value="Nome impresso no cartão" />
                    <x-text-input wire:model="holder_name" id="holder_name" class="mt-1 block w-full uppercase" maxlength="60" autocomplete="cc-name" />
                    <x-input-error :messages="$errors->get('holder_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="exp_month" value="Mês" />
                    <x-select-input wire:model="exp_month" id="exp_month" class="mt-1 block w-full" autocomplete="cc-exp-month">
                        <option value="">MM</option>
                        @foreach (range(1, 12) as $month)
                            <option wire:key="m-{{ $month }}" value="{{ $month }}">{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->get('exp_month')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="exp_year" value="Ano" />
                    <x-select-input wire:model="exp_year" id="exp_year" class="mt-1 block w-full" autocomplete="cc-exp-year">
                        <option value="">AAAA</option>
                        @foreach ($years as $year)
                            <option wire:key="y-{{ $year }}" value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->get('exp_year')" class="mt-2" />
                </div>

                <div class="sm:col-span-4 flex gap-3 justify-end">
                    <x-secondary-button type="button" wire:click="cancel">Cancelar</x-secondary-button>
                    <x-primary-button wire:loading.attr="disabled">Salvar cartão</x-primary-button>
                </div>
            </form>
        </x-card>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($cards as $card)
            <div wire:key="card-{{ $card->id }}" class="rounded-xl bg-gradient-to-br from-gray-800 to-gray-600 p-5 text-white shadow">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">{{ $card->brand->label() }}</span>
                    @if ($card->isExpired())
                        <span class="rounded bg-red-500 px-2 text-xs">Vencido</span>
                    @endif
                </div>
                <p class="mt-6 font-mono text-lg tracking-widest">{{ $card->maskedNumber() }}</p>
                <div class="mt-4 flex items-end justify-between text-sm">
                    <div>
                        <p class="uppercase">{{ $card->holder_name }}</p>
                        <p class="text-gray-300">Validade {{ $card->expiry() }}</p>
                    </div>
                    <button type="button" wire:click="delete({{ $card->id }})" wire:confirm="Remover este cartão?" class="underline text-gray-200 hover:text-white">Remover</button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Nenhum cartão cadastrado.</p>
        @endforelse
    </div>
</div>
