@use('App\Support\Money')

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header title="Meus produtos" subtitle="Anuncie produtos e acompanhe o estoque.">
        <a href="{{ route('seller.sales.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Minhas vendas</a>
        <a href="{{ route('seller.products.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Novo produto</a>
    </x-page-header>

    <x-card class="overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Produto</th>
                    <th class="px-4 py-3">Preço</th>
                    <th class="px-4 py-3">Estoque</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr wire:key="product-{{ $product->id }}" class="border-t">
                        <td class="px-4 py-3"><a href="{{ route('products.show', $product) }}" class="font-medium hover:underline">{{ $product->name }}</a></td>
                        <td class="px-4 py-3">{{ Money::format($product->price_cents) }}</td>
                        <td class="px-4 py-3">{{ $product->stock }}</td>
                        <td class="px-4 py-3">
                            <button type="button" wire:click="toggleActive({{ $product->id }})" class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                {{ $product->is_active ? 'Ativo' : 'Pausado' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('seller.products.edit', $product) }}" class="text-indigo-700 underline">Editar</a>
                            <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Excluir este produto?" class="text-red-700 underline">Excluir</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Você ainda não anunciou nenhum produto.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-6">{{ $products->links() }}</div>
</div>
