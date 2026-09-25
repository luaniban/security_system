<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function view(?User $user, Product $product): bool
    {
        return $product->is_active || $user?->id === $product->seller_id;
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->seller_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->seller_id;
    }

    public function purchase(User $user, Product $product): bool
    {
        return $product->is_active && $product->stock > 0 && $user->id !== $product->seller_id;
    }
}
