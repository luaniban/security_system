<?php

namespace App\Livewire\Seller;

use App\Services\AuditLogger;
use App\Services\ProductImageStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Meus produtos')]
class ListProducts extends Component
{
    use WithPagination;

    public function toggleActive(int $productId): void
    {
        $product = Auth::user()->products()->findOrFail($productId);
        $this->authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);
    }

    public function delete(int $productId, ProductImageStorage $images, AuditLogger $audit): void
    {
        $product = Auth::user()->products()->findOrFail($productId);
        $this->authorize('delete', $product);

        $images->delete($product->image_path);
        $product->delete();

        $audit->log('product.deleted', Auth::user(), ['product_id' => $productId]);
    }

    public function render(): View
    {
        return view('livewire.seller.list-products', [
            'products' => Auth::user()->products()->latest()->paginate(10),
        ]);
    }
}
