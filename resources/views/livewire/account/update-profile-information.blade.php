<x-card>
    <h2 class="text-lg font-medium text-gray-900">Informações pessoais</h2>
    <p class="mt-1 text-sm text-gray-600">Para trocar o e-mail de login é necessário informar sua senha atual.</p>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-4" novalidate>
        <div>
            <x-input-label for="name" value="Nome completo" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" maxlength="120" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="cpf" value="CPF" />
                <x-text-input id="cpf" type="text" class="mt-1 block w-full bg-gray-100" :value="$maskedCpf" disabled />
                <p class="mt-1 text-xs text-gray-500">O CPF não pode ser alterado.</p>
            </div>

            <div>
                <x-input-label for="phone" value="Celular" />
                <x-text-input wire:model="phone" id="phone" type="tel" class="mt-1 block w-full" maxlength="15" required autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" maxlength="255" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="profile_current_password" value="Senha atual (apenas se trocar o e-mail)" />
            <x-text-input wire:model="current_password" id="profile_current_password" type="password" class="mt-1 block w-full" maxlength="128" autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button wire:loading.attr="disabled">Salvar</x-primary-button>
            @if (session('profile-status'))
                <p class="text-sm text-green-700">{{ session('profile-status') }}</p>
            @endif
        </div>
    </form>
</x-card>
