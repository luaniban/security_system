<div class="max-w-3xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header :title="$productId ? 'Editar produto' : 'Novo produto'" />

    <x-card>
        <form wire:submit="save" class="space-y-5" novalidate>
            <div>
                <x-input-label for="name" value="Nome do produto" />
                <x-text-input wire:model="name" id="name" class="mt-1 block w-full" maxlength="120" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Descrição" />
                <textarea wire:model="description" id="description" rows="6" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                <p class="mt-1 text-xs text-gray-500">Texto simples. HTML não é interpretado.</p>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="price" value="Preço (R$)" />
                    <x-text-input wire:model="price" id="price" class="mt-1 block w-full" inputmode="decimal" maxlength="10" placeholder="99,90" />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="stock" value="Estoque" />
                    <x-text-input wire:model="stock" id="stock" type="number" min="0" max="10000" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600">
                        <span class="text-sm">Anúncio ativo</span>
                    </label>
                </div>
            </div>

            <div>
                <x-input-label for="image" value="Imagem (JPG, PNG ou WebP, até 2 MB)" />
                <input wire:model="image" id="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
                <div wire:loading wire:target="image" class="mt-1 text-xs text-gray-500">Enviando...</div>
                <x-input-error :messages="$errors->get('image')" class="mt-2" />

                <div class="mt-3 flex gap-4">
                    @if ($image && ! $errors->has('image') && method_exists($image, 'isPreviewable') && $image->isPreviewable())
                        <img src="{{ $image->temporaryUrl() }}" alt="Pré-visualização" class="h-32 w-32 rounded object-cover">
                    @elseif ($currentImageUrl)
                        <img src="{{ $currentImageUrl }}" alt="Imagem atual" class="h-32 w-32 rounded object-cover">
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('seller.products.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Cancelar</a>
                <x-primary-button wire:loading.attr="disabled">Salvar produto</x-primary-button>
            </div>
        </form>
    </x-card>
</div>
