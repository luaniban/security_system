<div>
    <h1 class="text-xl font-semibold mb-4">Entrar na sua conta</h1>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4" novalidate>
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" maxlength="255" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" maxlength="128" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember" class="inline-flex items-center">
            <input wire:model="remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="ms-2 text-sm text-gray-600">Manter conectado</span>
        </label>

        <div class="flex items-center justify-between">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                Esqueceu a senha?
            </a>

            <x-primary-button wire:loading.attr="disabled">Entrar</x-primary-button>
        </div>

        <p class="text-sm text-gray-600 text-center pt-2 border-t">
            Não tem conta?
            <a class="underline text-indigo-700" href="{{ route('register') }}">Cadastre-se</a>
        </p>
    </form>
</div>
