<x-card>
    <h2 class="text-lg font-medium text-gray-900">Excluir conta</h2>
    <p class="mt-1 text-sm text-gray-600">
        Seus dados pessoais, endereços e cartões serão apagados permanentemente (LGPD, art. 18).
    </p>

    @if (! $confirming)
        <x-danger-button type="button" class="mt-4" wire:click="$set('confirming', true)">Quero excluir minha conta</x-danger-button>
    @else
        <form wire:submit="deleteUser" class="mt-4 space-y-4 rounded-md border border-red-200 bg-red-50 p-4" novalidate>
            <p class="text-sm text-red-800">Esta ação não pode ser desfeita. Digite sua senha para confirmar.</p>

            <div>
                <x-input-label for="delete_password" value="Senha" />
                <x-text-input wire:model="password" id="delete_password" type="password" class="mt-1 block w-full sm:w-2/3" maxlength="128" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex gap-3">
                <x-secondary-button type="button" wire:click="$set('confirming', false)">Cancelar</x-secondary-button>
                <x-danger-button wire:loading.attr="disabled">Excluir definitivamente</x-danger-button>
            </div>
        </form>
    @endif
</x-card>
