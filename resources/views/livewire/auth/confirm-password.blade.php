<div>
    <h1 class="text-xl font-semibold mb-2">Área protegida</h1>
    <p class="mb-4 text-sm text-gray-600">
        Por segurança, confirme sua senha para continuar. Você não precisará confirmar novamente pelos próximos {{ intdiv(config('auth.password_timeout'), 60) }} minutos.
    </p>

    <form wire:submit="confirmPassword" class="space-y-4" novalidate>
        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" maxlength="128" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-primary-button wire:loading.attr="disabled">Confirmar</x-primary-button>
        </div>
    </form>
</div>
