<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-admin-nav />

    <x-page-header title="Usuários" subtitle="CPF e telefone aparecem sempre mascarados, inclusive para administradores." />

    <x-text-input wire:model.live.debounce.400ms="search" type="search" placeholder="Buscar por nome ou e-mail" maxlength="100" class="mb-4 w-full sm:w-80" />

    <x-card class="overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">E-mail</th>
                    <th class="px-4 py-3">CPF</th>
                    <th class="px-4 py-3">Telefone</th>
                    <th class="px-4 py-3">Compras</th>
                    <th class="px-4 py-3">Vendas</th>
                    <th class="px-4 py-3">Produtos</th>
                    <th class="px-4 py-3">Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="border-t">
                        <td class="px-4 py-3">{{ $user->name }} @if ($user->is_admin)<span class="ml-1 rounded bg-brand-100 px-1.5 text-xs text-brand-700">admin</span>@endif</td>
                        <td class="px-4 py-3">{{ $user->email }} @unless ($user->hasVerifiedEmail())<span class="text-xs text-amber-700">(não verificado)</span>@endunless</td>
                        <td class="whitespace-nowrap px-4 py-3 tabular-nums">{{ $user->maskedCpf() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 tabular-nums">{{ $user->maskedPhone() }}</td>
                        <td class="px-4 py-3">{{ $user->purchases_count }}</td>
                        <td class="px-4 py-3">{{ $user->sales_count }}</td>
                        <td class="px-4 py-3">{{ $user->products_count }}</td>
                        <td class="px-4 py-3">{{ $user->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>

    <div class="mt-6">{{ $users->links() }}</div>
</div>
