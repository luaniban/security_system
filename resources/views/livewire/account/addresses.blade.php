<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-account-nav />

    <x-page-header title="Endereços de entrega" subtitle="Seus endereços ficam guardados de forma criptografada e só aparecem para o vendedor no momento da entrega.">
        @unless ($showForm)
            <x-primary-button type="button" wire:click="create">Novo endereço</x-primary-button>
        @endunless
    </x-page-header>

    @if (session('address-status'))
        <p class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('address-status') }}</p>
    @endif

    @if ($showForm)
        <x-card class="mb-6">
            <h2 class="text-lg font-medium mb-4">{{ $editingId ? 'Editar endereço' : 'Novo endereço' }}</h2>

            <form wire:submit="save" class="grid gap-4 sm:grid-cols-6" novalidate>
                <div class="sm:col-span-2">
                    <x-input-label for="label" value="Identificação (ex.: Casa)" />
                    <x-text-input wire:model="label" id="label" class="mt-1 block w-full" maxlength="50" />
                    <x-input-error :messages="$errors->get('label')" class="mt-2" />
                </div>
                <div class="sm:col-span-4">
                    <x-input-label for="recipient_name" value="Destinatário" />
                    <x-text-input wire:model="recipient_name" id="recipient_name" class="mt-1 block w-full" maxlength="120" />
                    <x-input-error :messages="$errors->get('recipient_name')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="zip_code" value="CEP" />
                    <x-text-input wire:model="zip_code" id="zip_code" class="mt-1 block w-full" inputmode="numeric" maxlength="9" placeholder="00000-000" autocomplete="postal-code" />
                    <x-input-error :messages="$errors->get('zip_code')" class="mt-2" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="street" value="Rua" />
                    <x-text-input wire:model="street" id="street" class="mt-1 block w-full" maxlength="150" autocomplete="address-line1" />
                    <x-input-error :messages="$errors->get('street')" class="mt-2" />
                </div>
                <div class="sm:col-span-1">
                    <x-input-label for="number" value="Número" />
                    <x-text-input wire:model="number" id="number" class="mt-1 block w-full" maxlength="10" />
                    <x-input-error :messages="$errors->get('number')" class="mt-2" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="complement" value="Complemento" />
                    <x-text-input wire:model="complement" id="complement" class="mt-1 block w-full" maxlength="60" />
                    <x-input-error :messages="$errors->get('complement')" class="mt-2" />
                </div>
                <div class="sm:col-span-3">
                    <x-input-label for="district" value="Bairro" />
                    <x-text-input wire:model="district" id="district" class="mt-1 block w-full" maxlength="80" />
                    <x-input-error :messages="$errors->get('district')" class="mt-2" />
                </div>
                <div class="sm:col-span-4">
                    <x-input-label for="city" value="Cidade" />
                    <x-text-input wire:model="city" id="city" class="mt-1 block w-full" maxlength="80" autocomplete="address-level2" />
                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="state" value="UF" />
                    <x-select-input wire:model="state" id="state" class="mt-1 block w-full">
                        <option value="">Selecione</option>
                        @foreach ($states as $uf)
                            <option wire:key="uf-{{ $uf->value }}" value="{{ $uf->value }}">{{ $uf->value }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->get('state')" class="mt-2" />
                </div>

                <div class="sm:col-span-6 flex gap-3 justify-end">
                    <x-secondary-button type="button" wire:click="cancel">Cancelar</x-secondary-button>
                    <x-primary-button wire:loading.attr="disabled">Salvar endereço</x-primary-button>
                </div>
            </form>
        </x-card>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($addresses as $address)
            <x-card wire:key="address-{{ $address->id }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold">{{ $address->label }}</p>
                        <p class="text-sm text-gray-700 mt-1">{{ $address->recipient_name }}</p>
                        <p class="text-sm text-gray-600">{{ $address->street }}, {{ $address->number }}{{ $address->complement ? ' - '.$address->complement : '' }}</p>
                        <p class="text-sm text-gray-600">{{ $address->district }}, {{ $address->city }}/{{ $address->state->value }}</p>
                        <p class="text-sm text-gray-600">CEP {{ $address->formattedZipCode() }}</p>
                    </div>
                    <div class="flex flex-col gap-2 text-sm">
                        <button type="button" wire:click="edit({{ $address->id }})" class="text-brand-700 underline">Editar</button>
                        <button type="button" wire:click="delete({{ $address->id }})" wire:confirm="Excluir este endereço?" class="text-red-700 underline">Excluir</button>
                    </div>
                </div>
            </x-card>
        @empty
            <p class="text-sm text-gray-500">Nenhum endereço cadastrado.</p>
        @endforelse
    </div>
</div>
