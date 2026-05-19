<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->isSeller();
    }

    public function update(User $user, Product $product): bool
    {
        // Solo el vendedor dueño del listado, o un admin
        return $user->isAdmin() || $product->seller_id === $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin() || $product->seller_id === $user->id;
    }
}
