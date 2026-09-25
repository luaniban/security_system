<x-card>
    <h2 class="text-lg font-medium text-gray-900">Alterar senha</h2>
    <p class="mt-1 text-sm text-gray-600">Ao trocar a senha, todas as outras sessões abertas são encerradas.</p>

    <form wire:submit="updatePassword" class="mt-6 space-y-4" novalidate>
        <div>
            <x-input-label for="update_password_current_password" value="Senha atual" />
            <x-text-input wire:model="current_password" id="update_password_current_password" type="password" class="mt-1 block w-full" maxlength="128" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nova senha" />
            <x-text-input wire:model="password" id="update_password_password" type="password" class="mt-1 block w-full" maxlength="128" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirme a nova senha" />
            <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" type="password" class="mt-1 block w-full" maxlength="128" autocomplete="new-password" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button wire:loading.attr="disabled">Alterar senha</x-primary-button>
            @if ($status)
                <p class="text-sm text-green-700">{{ $status }}</p>
            @endif
        </div>
    </form>
</x-card>
