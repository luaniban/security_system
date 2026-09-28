<div>
    <h1 class="text-xl font-semibold mb-4">Criar conta</h1>

    <form wire:submit="register" class="space-y-4" novalidate>
        <div>
            <x-input-label for="name" value="Nome completo" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" maxlength="120" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" maxlength="255" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="cpf" value="CPF" />
                <x-text-input wire:model="cpf" id="cpf" class="block mt-1 w-full" type="text" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" required />
                <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" value="Celular" />
                <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="tel" maxlength="15" placeholder="(11) 91234-5678" required autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" maxlength="128" required autocomplete="new-password" />
            <p class="mt-1 text-xs text-gray-500">Mínimo de 12 caracteres, com maiúscula, minúscula, número e símbolo. Senhas vazadas são recusadas.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirme a senha" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" maxlength="128" required autocomplete="new-password" />
        </div>

        <div>
            <label for="terms" class="inline-flex items-start gap-2">
                <input wire:model="terms" id="terms" type="checkbox" class="mt-1 rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
                <span class="text-sm text-gray-600">
                    Li e concordo com a <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="underline">Política de Privacidade</a> e com o tratamento dos meus dados pessoais conforme a LGPD.
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">Já tenho conta</a>
            <x-primary-button wire:loading.attr="disabled">Cadastrar</x-primary-button>
        </div>
    </form>
</div>
