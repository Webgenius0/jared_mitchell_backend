<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Add a product to the cart. If it already exists, increment quantity.
     *
     * @return Cart
     */
    public function add(int $userId, int $productId, int $quantity = 1): Cart
    {
        $product = Product::active()->findOrFail($productId);

        // Validate stock
        if ($product->track_stock && $product->stock < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock. Only {$product->stock} available."
            );
        }

        $existing = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $newQuantity = $existing->quantity + $quantity;

            if ($product->track_stock && $product->stock < $newQuantity) {
                throw new \RuntimeException(
                    "Insufficient stock. Only {$product->stock} available in total."
                );
            }

            $existing->update(['quantity' => $newQuantity]);

            return $existing->fresh()->load('product');
        }

        return Cart::create([
            'user_id'    => $userId,
            'product_id' => $productId,
            'quantity'   => $quantity,
        ])->load('product');
    }

    /**
     * Update the quantity of a cart item.
     *
     * @return Cart
     */
    public function updateQuantity(int $userId, int $cartId, int $quantity): Cart
    {
        $cart = Cart::where('user_id', $userId)
            ->findOrFail($cartId);

        if ($quantity < 1) {
            throw new \RuntimeException('Quantity must be at least 1.');
        }

        $product = $cart->product;

        if ($product->track_stock && $product->stock < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock. Only {$product->stock} available."
            );
        }

        $cart->update(['quantity' => $quantity]);

        return $cart->fresh()->load('product');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $userId, int $cartId): bool
    {
        return Cart::where('user_id', $userId)
            ->where('id', $cartId)
            ->delete() > 0;
    }

    /**
     * List all items in the user's cart with product details.
     */
    public function list(int $userId): Collection
    {
        return Cart::where('user_id', $userId)
            ->with(['product' => function ($query) {
                $query->with(['category', 'images']);
            }])
            ->latest()
            ->get()
            ->map(function (Cart $item) {
                $item->setAppends(['subtotal']);
                return $item;
            });
    }

    /**
     * Clear the entire cart.
     */
    public function clear(int $userId): void
    {
        Cart::where('user_id', $userId)->delete();
    }

    /**
     * Get cart summary with totals.
     */
    public function summary(int $userId): array
    {
        $items = $this->list($userId);

        $subtotal = $items->sum(function ($item) {
            return $item->subtotal;
        });

        $totalItems = $items->sum('quantity');
        $uniqueItems = $items->count();

        return [
            'items'        => $items,
            'subtotal'     => round($subtotal, 2),
            'total_items'  => $totalItems,
            'unique_items' => $uniqueItems,
        ];
    }
}
