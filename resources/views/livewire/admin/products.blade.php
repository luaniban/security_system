<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-admin-nav />

    <x-page-header title="Produtos" subtitle="Todos os produtos da loja, de todos os vendedores, inclusive os pausados." />

    <div class="mb-4 flex flex-wrap gap-3">
        <x-text-input wire:model.live.debounce.400ms="search" type="search" placeholder="Buscar por nome ou descrição" maxlength="100" class="w-full sm:w-80" />
        <x-select-input wire:model.live="situation" aria-label="Filtrar situação">
            <option value="">Todas as situações</option>
            <option value="ativos">Ativos</option>
            <option value="pausados">Pausados</option>
        </x-select-input>
    </div>

    <x-card class="overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Produto</th>
                    <th class="px-4 py-3">Vendedor</th>
                    <th class="px-4 py-3 text-right">Preço</th>
                    <th class="px-4 py-3 text-right">Estoque</th>
                    <th class="px-4 py-3">Situação</th>
                    <th class="px-4 py-3">Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr wire:key="product-{{ $product->id }}" class="border-t">
                        <td class="px-4 py-3">
                            @if ($product->is_active)
                                <a href="{{ route('products.show', $product) }}" class="text-brand-700 underline">{{ $product->name }}</a>
                            @else
                                {{ $product->name }}
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $product->seller->name }}<br><span class="text-xs text-gray-500">{{ $product->seller->email }}</span></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ \App\Support\Money::format($product->price_cents) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $product->stock }}</td>
                        <td class="px-4 py-3">
                            @if ($product->is_active)
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Ativo</span>
                            @else
                                <span class="rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700">Pausado</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $product->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Nenhum produto encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-6">{{ $products->links() }}</div>
</div>
