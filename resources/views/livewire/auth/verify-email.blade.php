<div>
    <h1 class="text-xl font-semibold mb-2">Confirme seu e-mail</h1>
    <p class="mb-4 text-sm text-gray-600">
        Enviamos um link de confirmação para <strong>{{ auth()->user()->email }}</strong>. Para comprar ou vender é necessário confirmar o e-mail.
    </p>

    <x-auth-session-status class="mb-4" :status="$status" />

    <div class="flex items-center justify-between">
        <x-primary-button type="button" wire:click="sendVerification" wire:loading.attr="disabled">Reenviar e-mail</x-primary-button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">Sair</button>
        </form>
    </div>
</div>
