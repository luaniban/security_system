<div>
    <h1 class="text-xl font-semibold mb-2">Esqueceu sua senha?</h1>
    <p class="mb-4 text-sm text-gray-600">
        Informe seu e-mail e enviaremos um link para você criar uma nova senha. O link expira em {{ config('auth.passwords.users.expire') }} minutos e só pode ser usado uma vez.
    </p>

    <x-auth-session-status class="mb-4" :status="$status" />

    <form wire:submit="sendResetLink" class="space-y-4" novalidate>
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" maxlength="255" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">Voltar ao login</a>
            <x-primary-button wire:loading.attr="disabled">Enviar link</x-primary-button>
        </div>
    </form>
</div>
