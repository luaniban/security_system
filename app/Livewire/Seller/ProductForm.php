<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\ProductImageStorage;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

class ProductForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $productId = null;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public int|string $stock = 1;

    public bool $is_active = true;

    /** @var TemporaryUploadedFile|null */
    public $image = null;

    public ?string $currentImageUrl = null;

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            $this->authorize('update', $product);

            $this->productId = $product->id;
            $this->name = $product->name;
            $this->description = $product->description;
            $this->price = Money::toInput($product->price_cents);
            $this->stock = $product->stock;
            $this->is_active = $product->is_active;
            $this->currentImageUrl = $product->imageUrl();

            return;
        }

        $this->authorize('create', Product::class);
    }

    public function save(ProductImageStorage $images, AuditLogger $audit): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'price' => ['required', 'string', 'regex:'.Money::PRICE_PATTERN],
            'stock' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000'],
        ], [
            'price.regex' => 'Informe o preço no formato 1234,56.',
        ]);

        $priceCents = Money::toCents($validated['price']);

        if ($priceCents === null || $priceCents < 100) {
            throw ValidationException::withMessages(['price' => 'O preço mínimo é R$ 1,00.']);
        }

        $product = $this->productId
            ? Auth::user()->products()->findOrFail($this->productId)
            : new Product;

        $product->exists ? $this->authorize('update', $product) : $this->authorize('create', Product::class);

        $attributes = [
            'name' => trim($validated['name']),
            'description' => trim($validated['description']),
            'price_cents' => $priceCents,
            'stock' => (int) $validated['stock'],
            'is_active' => $validated['is_active'],
        ];

        if ($this->image) {
            try {
                $newPath = $images->store($this->image);
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages(['image' => $exception->getMessage()]);
            }

            $images->delete($product->image_path);
            $attributes['image_path'] = $newPath;
        }

        $product->fill($attributes);

        // seller_id is set explicitly (never mass-assigned from input).
        if (! $product->exists) {
            $product->seller_id = Auth::id();
        }

        $product->save();

        $audit->log($this->productId ? 'product.updated' : 'product.created', Auth::user(), ['product_id' => $product->id]);

        session()->flash('status', 'Produto salvo.');
        $this->redirect(route('seller.products.index'));
    }

    public function render(): View
    {
        return view('livewire.seller.product-form')
            ->title($this->productId ? 'Editar produto' : 'Novo produto');
    }
}
