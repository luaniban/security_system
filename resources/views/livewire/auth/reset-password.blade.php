<div>
    <h1 class="text-xl font-semibold mb-4">Criar nova senha</h1>

    <form wire:submit="resetPassword" class="space-y-4" novalidate>
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" maxlength="255" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Nova senha" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" maxlength="128" required autofocus autocomplete="new-password" />
            <p class="mt-1 text-xs text-gray-500">Mínimo de 12 caracteres, com maiúscula, minúscula, número e símbolo.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirme a nova senha" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" maxlength="128" required autocomplete="new-password" />
        </div>

        <div class="flex justify-end">
            <x-primary-button wire:loading.attr="disabled">Redefinir senha</x-primary-button>
        </div>
    </form>
</div>
